<?php
namespace Modules\Extend\Traits;
use Carbon\Carbon;

trait Updater {

    public function myLog(){
        $mylog_path = \mb_substr(\get_class($this), 0, -\mb_strlen(class_basename($this))).'Mylog';
        return $this->hasMany($mylog_path, 'id_tbl', 'id')->where('tbl', $this->table)->whereRaw('id_approvaz!=""');
    }

    public function cambiaStato($stato){
        if ('' == $stato) {
            $stato = 1;
        }
        $mylog_path = \mb_substr(\get_class($this), 0, -\mb_strlen(class_basename($this))).'Mylog';
        $log = new $mylog_path();
        $log->id_approvaz = $stato;
        $log->tbl = $this->table;
        $log->save();
        if (!\method_exists($this, 'myLog')) {
            echo '<hr/>mylog_path : ['.$mylog_path.']';
            ddd($this);
        }
        $res = $this->myLog()->save($log);
        $this->last_stato = $stato;
        $this->datemod = Carbon::now();
        $this->handle = \Auth::user()->handle;
        $this->save();
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    protected static function boot(){
        parent::boot();
        /**
        * During a model create Eloquent will also update the updated_at field so 
        * need to have the updated_by field here as well 
        **/
        static::creating(function ($model) {
            if (null != \Auth::user()) {
                $model->created_by = \Auth::user()->handle;
                $model->updated_by = \Auth::user()->handle.'';
            }
        });

        static::updating(function ($model) {
            if (\Auth::check()) {
                $model->updated_by = \Auth::user()->handle.'';
            }
        });
        //-------------------------------------------------------------------------------------
        /**
         * Deleting a model is slightly different than creating or deleting. 
         * For deletes we need to save the model first with the deleted_by field
        **/
        /*
        static::deleting(function ($model) {
            $model->deleted_by = \Auth::user()->handle;
            $model->save();
        });
        */
        //----------------------
    }//end function boot
}//end trait Updater

