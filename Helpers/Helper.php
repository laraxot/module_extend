<?php

use Illuminate\Support\Arr;


//namespace Modules\XRA\Helpers;

if (!\function_exists('ddd')) {
    function ddd($params)
    {
        /*
        try{
            \header('Content-type: text/html');
        }catch(\Exception $e)
                dd($e);
            // headers already sent
        }
        */
        $tmp = \debug_backtrace();
        $file = $tmp[0]['file'];
        $file = \str_replace('/', DIRECTORY_SEPARATOR, $file);
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        $doc_root = \str_replace('/', DIRECTORY_SEPARATOR, $doc_root);
        $dir_piece = \explode(DIRECTORY_SEPARATOR, __DIR__);
        $dir_piece = \array_slice($dir_piece, 0, -6);
        $dir_copy = \implode(DIRECTORY_SEPARATOR, $dir_piece);
        $file = \str_replace($dir_copy, $doc_root, $file);
        echo '<h3>LINE: ['.$tmp[0]['line'].']<br/>
		FILE: ['.$file.']<br/>
		</h3>';
        dd($params);
    }
}

if (!\function_exists('getFilename')) {
    function getFilename($params)
    {
        $tmp = \debug_backtrace();
        $class = class_basename($tmp[1]['class']);
        $func = $tmp[1]['function'];
        $params_list = collect($params)->except(['_token', '_method'])->implode('_');
        $filename = str_slug(
            \str_replace('Controller', '', $class).
                    '_'.\str_replace('do_', '', $func).
                    '_'.$params_list
                );

        return $filename;
    }
}

if (!\function_exists('setConfig')) {
    function setConfig($params)
    {
        $data = getConfig($params);
        $data = \array_merge($data, $params['data']);

        $config_files = getConfigFiles($params);
        if (\count($config_files) > 1) {
            foreach ($config_files as $k => $file) {
                arraySave(['filename' => $config_files[$k], 'data' => $data[$k]]);
            }
        } else {
            arraySave(['filename' => $config_files[0], 'data' => $data]);
        }
        //\Session::flash('status', $params['msg'].' '.\Carbon\Carbon::now());
        //return \Redirect::back();
    }
}

if (!\function_exists('getConfig')) {
    function getConfig($params)
    {
        $config_files = getConfigFiles($params);
        if (\count($config_files) > 1) {
            $data = [];
            foreach ($config_files as $k => $config_file) {
                $tmp = include $config_file;
                $data[$k] = $tmp;
            }
        } else {
            $data = include $config_files[0];
        }

        return $data;
    }
}

if (!\function_exists('getConfigFile')) {
    function getConfigFiles($params)
    {
        //if(count($params)>1){
        if (\is_dir(base_path('config/'.$params['file']))) {
            $tmps = (\array_keys(config($params['file'])));
            $files = [];
            foreach ($tmps as $tmp) {
                $files[$tmp] = base_path('config'.DIRECTORY_SEPARATOR.$params['file'].DIRECTORY_SEPARATOR.$tmp.'.php');
            }

            return $files;
        }
        //ddd($params);
        //}
        if (!isset($_SERVER['SERVER_NAME']) || '127.0.0.1' == $_SERVER['SERVER_NAME']) {
            $_SERVER['SERVER_NAME'] = 'localhost';
        }
        $server_name = str_slug(\str_replace('www.', '', $_SERVER['SERVER_NAME']));
        $config_file = base_path('config'.DIRECTORY_SEPARATOR.$server_name.DIRECTORY_SEPARATOR.$params['file']);
        if (!\file_exists($config_file)) {
            //ddd($config_file);
            if (\file_exists(base_path('config/'.$params['file']))) {
                //ddd(base_path('config/'.$params['file']));
                return [base_path('config/'.$params['file'])];
            }
            if (\file_exists(base_path('config/'.$params['file'].'.php'))) {
                //ddd('b');
                return [base_path('config/'.$params['file'].'.php')];
            }
            echo '<h3>'.$config_file.'</h3>';
            dd('<br/>LINE:['.__LINE__.']['.__FILE__.']');
        }
        return [$config_file];
    }
}

if (!\function_exists('arraySave')) {
    function arraySave($params)
    {
        \XRA\Extend\Services\ArrayService::save($params);
        /*
        \extract($params);
        $writer = new Zend\Config\Writer\PhpArray();
        $content = $writer->toString($data);
        $content = \str_replace('\\\\', '\\', $content);
        $content = \str_replace('\\\\', '\\', $content);
        //$content=str_replace("\\'","\'", $content);
        $content = \str_replace("'".storage_path(), 'storage_path()'.".'", $content);
        \File::put($filename, $content);
        */
    }
}

if (!\function_exists('in_admin')) {
    function in_admin()
    {
        return 'admin' == \Request::segment(1);
    }
}
if (!\function_exists('inAdmin')) {
    function inAdmin()
    {
        return 'admin' == \Request::segment(1);
    }
}


    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
if (!\function_exists('fullTextWildcards')) {
    /*protected */ function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);

        foreach($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if(strlen($word) >= 3) {
                $words[$key] = '+' . $word . '*';
            }
        }

        $searchTerm = implode( ' ', $words);

        return $searchTerm;
    }
}

if (!\function_exists('params2ContainerItem')) {
    function params2ContainerItem($params=null){
        if($params==null){
            $params = \Route::current()->parameters();
        }
        $container=[];
        $item=[];
        foreach($params as $k=>$v){
            $pattern='/(container|item)([0-9]+)/';
            preg_match($pattern, $k,$matches);
            if(isset($matches[1]) && isset($matches[2]) ){
                $sk=$matches[1];
                $sv=$matches[2];
                $$sk[$sv]=$v;
            };
        }
        return [$container,$item];
    }
}


if (!\function_exists('getModuleModels')) {
    function getModuleModels($module){
        if(Str::startsWith($module,'trasferte')){ //caso eccezzionale 
            $module='trasferte';
        }
        $mod=\Module::find($module);
        if($mod==null) return [];
        $mod_path=$mod->getPath().'/Models';
        $mod_path=str_replace(['\\','/'],[DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR],$mod_path);
        $files = File::files($mod_path);
        $data=[]; 
        $ns='Modules\\'.$mod->name.'\\Models';  // con la barra davanti non va il search ?
        foreach($files as $file){
            $filename=$file->getRelativePathname();
            $ext='.php';
            if(ends_with($filename,$ext)){
                $tmp=new \stdClass();
                $name=substr(($filename),0,-strlen($ext));
                $tmp->class=$ns.'\\'.$name;
                $name=Str::snake($name);
                $tmp->name=$name;
                $data[$tmp->name]=$tmp->class;
                
            }
        }
        return $data;
    }
}

if (!\function_exists('tenantName')) {
    function tenantName($params=[]){
        if (!isset($_SERVER['SERVER_NAME']) || '127.0.0.1' == $_SERVER['SERVER_NAME']) {
            $_SERVER['SERVER_NAME'] = 'localhost';
        }
        $server_name = str_slug(\str_replace('www.', '', $_SERVER['SERVER_NAME']));
        if(!\File::exists(base_path('config/'.$server_name))){
            $server_name = 'localhost';
        }
        return $server_name;
    }//end function
}

if (!\function_exists('xotModel')) {
    function xotModel($name){
        $model=tenantConfig('xra.model.'.$name);
        return new $model;
    }
}    

if (!\function_exists('tenantConfig')) {
    function tenantConfig($key){
        $group=implode('.',array_slice(explode('.',$key),0,2));
        if(in_admin() && Str::startsWith($key,'xra.model') ){
            $module_name=\Request::segment(2);
            $models=getModuleModels($module_name);
            $original_conf=config('xra.model');
            if(!is_array($original_conf))$original_conf=[];
            $merge_conf=array_merge($original_conf,$models);
            \Config::set('xra.model', $merge_conf);
        }
        $tenant_name=tenantName();
        $extra_conf=config($tenant_name.'.'.$group);
        $original_conf=config($group);
        //ddd($extra_conf);
        if(!is_array($original_conf)) $original_conf=[];
        if(!is_array($extra_conf)) $extra_conf=[];
        $merge_conf=array_merge($original_conf,$extra_conf); //_recursive
        \Config::set($group, $merge_conf);  // non so se metterlo ..
        return config($key);
    }
}

if (!\function_exists('transFields')) {
    function transFields($params){
        extract($params);
        if(isset($attributes)){
            extract($attributes);
        }
        $ris=new \stdClass();

        $start=0;
        if(in_admin()){
            $start=1;
        }
       
        $ris->name_dot=bracketsToDotted($name);

        $pattern= '/\.[0-9]+\./m';
        $ris->name_dot=preg_replace($pattern,'.',$ris->name_dot);
        
        list($ns,$key)=explode('::',$view);
        $view_noact=$ns.'::'.implode('.',array_slice(explode('.',$key),$start,-1));

        $trans_fields=['label','placeholder','help'];
        foreach($trans_fields as $tf){
            $trans=$view_noact.'.field.'.$ris->name_dot.'_'.$tf;
            $ris->$tf=isset($$tf)?$$tf:trans($trans);
            if($ris->$tf == $trans) $ris->$tf='';
        }
 
        /*
        $trans=$view_noact.'.field.'.$name;
        $ris->label=isset($label)?$label:trans($trans);
        //if($ris->label==$trans) $ris->label=$name;
        $trans=$view_noact.'.field.'.$name.'_placeholder';
        $ris->placeholder=trans($trans);
        if($ris->placeholder==$trans) $ris->placeholder=' ';
        */

        $attributes=$params;
        $attrs_default=['class' => 'form-control','placeholder'=>$ris->placeholder];
        $ris->attributes=collect(array_merge($attrs_default, $attributes))
                        ->filter(function($item,$key){
                            return in_array($key,['class','placeholder','readonly']) || Str::startsWith($key,'data-');
                        })
                        //->only('class','placeholder','readonly')
                        ->all();
        $ris->params=$params;
        return $ris;
    }
}

if (!\function_exists('debug_getter_obj')){
    function debug_getter_obj($params){
        extract($params);
        $methods=collect(get_class_methods($obj))->filter(function($item){
            $exclude=[
                //--Too few arguments to function
                'getRelationExistenceQuery',  
                'getRelationExistenceQueryForSelfRelation',
                'getRelationExistenceCountQuery',
                'getMorphedModel',
                'getRelationExistenceQueryForSelfJoin',
                'getPlatformOption',
                'getCustomSchemaOption',
                'getShortestName',
                'getFullQualifiedName',
                'getQuotedName',
                //---
                'getAttribute',
                'getAttributeValue',
                'getRelationValue',
                'getGlobalScope',
                'getActualClassNameForMorph',
                'getRelation',
                //---------
                'getDataStartAttribute',
                'getDataAttribute',
                //--altri errori --
            ];
            return (Str::startsWith($item,'get')  && !in_array($item,$exclude)  );
        })->map(function($item) use($obj) {
            $tmp=[];
            $tmp['name']=$item;
            try{
                $tmp['ris']=$obj->$item();
            }catch(\Exception $e){
                $tmp['ris']=$e->getMessage();
            }
            return $tmp;
        });
        //->dd();
        $html='<table border="1">
        <thead>
        <tr>
        <th>Name</th>
        <th>Ris</th>
        </tr>
        </thead>';
        foreach($methods as $k=>$v){
            $html.='<tr>';
            $html.='<td>'.$v['name'].'</td>';
            $val=$v['ris'];
            if(is_object($val)){
                $val='(Object) '.get_class($val);
            }
            if(is_array($val)){
                $val=var_export($val,true);
            }
            $html.='<td>'.$val.'</td>';
            $html.='</tr>';
            
        }
        $html.='</table>';
        echo $html;
        ddd($methods);
    }//end function
}//end exists


if (!\function_exists('bracketsToDotted')) {
 // privacies[111][pivot][title] => privacies.111.pivot.title 
    function bracketsToDotted($str,$quotation_marks=''){
        return str_replace(['[',']'],['.',''],$str);
    }
}
if (!\function_exists('dottedToBrackets')) {
 // privacies.111.pivot.title => privacies[111][pivot][title] 
    function dottedToBrackets($str,$quotation_marks=''){
        $str=collect(explode('.',$str))->map(function ($v, $k){
            return $k==0?$v:'['.$v.']';
        })->implode('');
        return $str;
    }
}




if (!\function_exists('money_format')) {
    // funzione copiata da https://php.net/manual/en/function.money-format.php
    // Improvement to Rafael M. Salvioni's solution for money_format on Windows: when no currency symbol is selected, in the formatting, the minus sign was also lost when the locale puts it in position 3 or 4. Changed $currency = '';  to: $currency = $cprefix .$csuffix;

    function money_format($format, $number) {
            $regex = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' .
                    '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
            if (setlocale(LC_MONETARY, 0) == 'C') {
                setlocale(LC_MONETARY, '');
            }
            $locale = localeconv();
            preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
            foreach ($matches as $fmatch) {
                $value = floatval($number);
                $flags = array(
                    'fillchar' => preg_match('/\=(.)/', $fmatch[1], $match) ?
                            $match[1] : ' ',
                    'nogroup' => preg_match('/\^/', $fmatch[1]) > 0,
                    'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                            $match[0] : '+',
                    'nosimbol' => preg_match('/\!/', $fmatch[1]) > 0,
                    'isleft' => preg_match('/\-/', $fmatch[1]) > 0
                );
                $width = trim($fmatch[2]) ? (int) $fmatch[2] : 0;
                $left = trim($fmatch[3]) ? (int) $fmatch[3] : 0;
                $right = trim($fmatch[4]) ? (int) $fmatch[4] : $locale['int_frac_digits'];
                $conversion = $fmatch[5];

                $positive = true;
                if ($value < 0) {
                    $positive = false;
                    $value *= -1;
                }
                $letter = $positive ? 'p' : 'n';

                $prefix = $suffix = $cprefix = $csuffix = $signal = '';

                $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
                switch (true) {
                    case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                        $prefix = $signal;
                        break;
                    case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                        $suffix = $signal;
                        break;
                    case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                        $cprefix = $signal;
                        break;
                    case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                        $csuffix = $signal;
                        break;
                    case $flags['usesignal'] == '(':
                    case $locale["{$letter}_sign_posn"] == 0:
                        $prefix = '(';
                        $suffix = ')';
                        break;
                }
                if (!$flags['nosimbol']) {
                    $currency = $cprefix .
                            ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                            $csuffix;
                } else {
                    $currency = $cprefix .$csuffix;
                }
                $space = $locale["{$letter}_sep_by_space"] ? ' ' : '';

                $value = number_format($value, $right, $locale['mon_decimal_point'], $flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
                $value = @explode($locale['mon_decimal_point'], $value);

                $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
                if ($left > 0 && $left > $n) {
                    $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
                }
                $value = implode($locale['mon_decimal_point'], $value);
                if ($locale["{$letter}_cs_precedes"]) {
                    $value = $prefix . $currency . $space . $value . $suffix;
                } else {
                    $value = $prefix . $value . $space . $currency . $suffix;
                }
                if ($width > 0) {
                    $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                                    STR_PAD_RIGHT : STR_PAD_LEFT);
                }

                $format = str_replace($fmatch[0], $value, $format);
            }
            return $format;
        }


}
