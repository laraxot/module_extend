<?php



namespace Modules\Extend\Traits;

//https://m.dotdev.co/writing-advanced-eloquent-search-query-filters-de8b6c2598db
//https://medium.com/@freekmurze/searching-models-using-a-where-like-query-in-laravel-7b6127d98054

use Illuminate\Support\Facades\Schema;
use TeamTNT\TNTSearch\TNTSearch;

trait FilterTrait
{
    public static function fields()
    {
        $rows = new self();
        $schema = $rows->getConnection()->getSchemaBuilder();
        $tbl = $rows->getTable();
        $fields = collect($schema->getColumnListing($tbl));

        return $fields;
    }

    public static function indexIfNotExistsStatic($index, $tbl = null, $conn = null)
    { //viene chiamato all'interno di filtertrait che e' static ..
        if (null == $tbl) {
            $self = new self();
            $tbl = $self->getTable();
            if (null == $conn) {
                $conn = $self->getConnection();
            }
        }
        if (\is_array($index)) {
            foreach ($index as $i) {
                self::indexIfNotExistsStatic($i, $tbl, $conn);
            }
        } else {
            $dbSchemaManager = $conn->getDoctrineSchemaManager();
            $doctrineTable = $dbSchemaManager->listTableDetails($tbl);
            //faremo dei controlli per non aggiungere troppe chiavi
            /*-- metodo alternativo da testare
            if (collect(DB::select("SHOW INDEXES FROM persons"))->pluck('Key_name')->contains('persons_body_unique')) {
                $table->dropUnique('persons_body_unique');
            }
            */
            /*-- altro metodo da testare 
                $indexesFound = $dbSchemaManager->listTableIndexes($tbl);
            */
            try{
                if (!$doctrineTable->hasIndex($tbl.'_'.$index.'_index')) {
                    Schema::connection($conn->getName())->table($tbl, function ($table) use ($index) {
                        $table->index($index);
                    });
                }
            }catch(\Exception $e){
                echo '<small>'.$e->getMessage().'</small>';
            }
        }
    }

    public function indexIfNotExists($index, $tbl = null, $conn = null)
    {
        if (null == $tbl) {
            $tbl = $this->getTable();
        }
        if (null == $conn) {
            $conn = $this->getConnection();
        }
        if (\is_array($index)) {
            foreach ($index as $i) {
                $this->indexIfNotExists($i, $tbl, $conn);
            }
        } else {
            $dbSchemaManager = $conn->getDoctrineSchemaManager();
            $doctrineTable = $dbSchemaManager->listTableDetails($tbl);
            //faremo dei controlli per non aggiungere troppe chiavi
            if (!$doctrineTable->hasIndex($tbl.'_'.$index.'_index')) {
                Schema::connection($conn->getName())->table($tbl, function ($table) use ($index) {
                    $table->index($index);
                });
            }
        }
    }

    public function scopeOfSearch($query, $q)
    {
        $model = $query->getModel();
        $tbl = $model->getTable();
        $conn = $model->getConnection();
        $schema = $conn->getSchemaBuilder();
        $fields = collect($schema->getColumnListing($tbl));
        //return $query;
        //return $query->whereRaw('1=1');
        /*
        foreach($fields as $k => $v){
            if($k==0){
                $query=$query->where($v,$q);
            }else{
                $query=$query->orWhere($v,$q);
            }
        }
        */

        $tmp = $model->search($q)->get()->map(function ($item) {
            return $item->id;
        });
        //ddd($tmp->all());
        return $query->whereIn('id', $tmp->all());
        //ddd($tmp);

        $index = $model->searchableAs().'.index';
        $driver = config('database.default');
        $config = config('scout.tntsearch') + config("database.connections.$driver");
        $candyShopIndex = new TNTSearch();
        $candyShopIndex->loadConfig($config);
        $candyShopIndex->selectIndex($index);
        $candyShops = $candyShopIndex->search($q);
        //ddd($res);
        //ddd($candyShops['ids']);
        //return $query;
        return $query->whereIn('id', $candyShops['ids']);
    }

    public function scopeOfFilter1($query, $params)
    {
        $model = $query->getModel();
        $tbl = $model->getTable();
        $conn = $model->getConnection();
        $schema = $conn->getSchemaBuilder();
        $fields = collect($schema->getColumnListing($tbl));

        $trad = \explode('\\', __CLASS__);
        $trad = \mb_strtolower($trad[1]);
        $lang = trans($trad.'::link_'.$tbl);
        if (!\is_array($lang)) {
            $lang = [];
        }
        $lang = \array_flip($lang);

        $parz = collect(\array_keys($params));
        /*
        $parz=$parz->map(function ($item, $key) use ($lang) {
            if (isset($lang[$item])) {
                return $lang[$item];
            }
            return $item;
        });;
        //*/
        $where = [];
        $whereRaw = [];
        $intersect = $fields->intersect($parz);
        foreach ($intersect->all() as $el) {
            $key = trans($trad.'::'.$tbl.'.'.$el);
            $key_link = trans($trad.'::link_'.$tbl.'.'.$el);
            if ('' != $key_link) {
                $key = $key_link;
            }
            if (!isset($params[$key])) {
                echo '<h3>parametro non passato '.$key.'</h3><pre>';
                \print_r($params);
                echo '</pre>';
                die('<br/>['.__LINE__.']['.__FILE__.']');
            } else {
                $value = $params[$key];
                //self::indexIfNotExistsStatic($el, $tbl, $conn);
                //dd(new self);
                (new self())->indexIfNotExists($el);
                //$rows=$rows->where($el, $value);
                $where[$el] = $value;
                $query = $query->where($el, $value);
            }
        }
        if (isset($lang['ann'])) {
            //self::indexIfNotExistsStatic($lang['ann'], $tbl, $conn);
            (new self())->indexIfNotExists($lang['ann']);
            //$rows->indexIfNotExists($lang['ann']);
            //$rows=$rows->whereRaw($lang['ann'].'=""');
            $whereRaw[] = $lang['ann'].'=""';
            $query = $query->whereRaw($lang['ann'].'=""');
        }
        if (isset($params['date_between'])) {
            \extract($params['date_between']);
            $from_time = \strtotime($from);
            $to_time = \strtotime($to);
            if (\Lang::has($trad.'::'.$tbl.'.single_date')) {
                $date_field = trans($trad.'::'.$tbl.'.single_date');
                $query = $query->whereRaw(
                    $date_field.' between '.\date('Ymd', $from_time).' and '.\date('Ymd', $to_time)
                );
            } elseif (isset($lang['giorno']) && isset($lang['mese']) && isset($lang['anno'])) {
                //die('so qua');
                $date_field = '('.$lang['anno'].'*10000+'.$lang['mese'].'*100+'.$lang['giorno'].')';
                $query = $query->whereRaw(
                    $date_field.' between '.\date('Ymd', $from_time).' and '.\date('Ymd', $to_time)
                );
            } else {
                die('solo single_date ['.__LINE__.']['.__FILE__.']');
            }
        }
        //ddd($parz);
        return $query;
    }

    public static function filter($params)
    {
        
        \ini_set('max_execution_time', 6000);
        \set_time_limit(0);
        $rows = new self();
        \extract($params);
        //if(is_callable([$rows,'getTable'])){
        if (\method_exists($rows, 'getTable')) {
            $tbl = $rows->getTable();
            $conn = $rows->getConnection();
        } else {
            //---- se prima c'e' stato una search da scout
            $tbl = $rows->model->getTable();
            $conn = $rows->model->getConnection();
        }
        //-----------------------

        //echo '<pre>';print_r($params);echo '</pre>';
        //$dbSchemaManager = $conn->getDoctrineSchemaManager();
        //$doctrineTable = $dbSchemaManager->listTableDetails($tbl);
        //dd($doctrineTable);
        //------------------------
        $schema = $conn->getSchemaBuilder();
        $fields = collect($schema->getColumnListing($tbl));

        $trad = \explode('\\', __CLASS__);
        $trad = \mb_strtolower($trad[1]);
        //dd($params);
        //dd($trad.'::'.$tbl);
        $lang = trans($trad.'::'.$tbl);
        //dd($lang);

        if (!\is_array($lang)) {
            $lang = [];
        } //anche se non esiste

        $trans_file = $trad.'::link_'.$tbl;
        //dd($trans_file);
        $lang_link = trans($trans_file);
        //dd($lang_link);
        if (\is_array($lang_link)) {
            $lang = $lang_link;
        }

        $lang = \array_flip($lang);
        //echo '<pre>';print_r($lang);echo '</pre>';
        //dd(get_class());

        //echo '<pre>';print_r($lang);echo '</pre>';
        $parz = collect(\array_keys($params));
        //dd($parz);
        $parz = $parz->map(function ($item, $key) use ($lang) {
            if (isset($lang[$item])) {
                return $lang[$item];
            }

            return $item;
        });
        $intersect = $fields->intersect($parz);
        //dd($intersect);
        foreach ($intersect->all() as $el) {
            $key = trans($trad.'::'.$tbl.'.'.$el);
            $key_link = trans($trad.'::link_'.$tbl.'.'.$el);
            if ('' != $key_link) {
                $key = $key_link;
            }
            if (!isset($params[$key])) {
                echo '<h3>parametro non passato '.$key.'</h3><pre>';
                \print_r($params);
                echo '</pre>';
                die('<br/>['.__LINE__.']['.__FILE__.']');
            } else {
                $value = $params[$key];
                //self::indexIfNotExistsStatic($el, $tbl, $conn);
                //dd(new self);
                (new self())->indexIfNotExists($el);
                $rows = $rows->where($el, $value);
            }
        }
        if (isset($lang['ann'])) {
            //self::indexIfNotExistsStatic($lang['ann'], $tbl, $conn);
            (new self())->indexIfNotExists($lang['ann']);
            //$rows->indexIfNotExists($lang['ann']);
            $rows = $rows->whereRaw($lang['ann'].'=""');
        }
        //dd(self::$single_date);
        //dd(\Lang::has($trad.'::'.$tbl.'.single_date'));
        //dd(trans($trad.'::'.$tbl.'.single_date'));

        //echo '<pre>';print_r($params);echo '</pre>';
        if (isset($params['date_between'])) {
            \extract($params['date_between']);
            $from_time = \strtotime($from);
            $to_time = \strtotime($to);
            if (\Lang::has($trad.'::'.$tbl.'.single_date')) {
                $date_field = trans($trad.'::'.$tbl.'.single_date');
                $rows = $rows->whereRaw(
                $date_field.' between '.\date('Ymd', $from_time).' and '.\date('Ymd', $to_time)
            );
            } elseif (isset($lang['giorno']) && isset($lang['mese']) && isset($lang['anno'])) {
                //die('so qua');
                $date_field = '('.$lang['anno'].'*10000+'.$lang['mese'].'*100+'.$lang['giorno'].')';
                $rows = $rows->whereRaw(
                $date_field.' between '.\date('Ymd', $from_time).' and '.\date('Ymd', $to_time)
            );
            } else {
                die('solo single_date ['.__LINE__.']['.__FILE__.']');
            }
        }
        //echo '<pre>'.$rows->toSql().'</pre>';die('['.__LINE__.']['.__FILE__.']');
        return $rows;
    }

    //end filter
}
