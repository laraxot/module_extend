<?php
namespace Modules\Extend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class StubService{



	public static function getByModel($model,$name,$create = false){
		$class_full=get_class($model);
		$class_name=class_basename($model);
		$class=Str::before($class_full,$class_name);
		$panel=$class.Str::plural(Str::studly($name)).'\\'.$class_name.Str::studly($name);
		try{
			if(class_exists($panel)){
				return new $panel;
			}
		}catch(\Exception $e){
			//ddd($e);
		}
		if(!$create){
			ddd($panel.' NOT EXISTS !');
		}
		self::create($model,$name);
		//return new $panel;
		ddd('Panel is under creating , refresh page');
		\Session::flash('status', $name.' created');
		//return redirect()->back();
	}

	public static function create($model,$name){
		$class_full=get_class($model);
		$class_name=class_basename($model);
		$class=Str::before($class_full,$class_name);
		$panel_namespace=$class.Str::plural(Str::studly($name));
		$panel=$panel_namespace.'\\'.$class_name.Str::studly($name);
		//---- creazione panel
		$autoloader_reflector = new \ReflectionClass($model);
		$class_file_nanme = $autoloader_reflector->getFileName();
		$model_dir=dirname($class_file_nanme);// /home/vagrant/code/htdocs/lara/multi/laravel/Modules/LU/Models
		$stub_file=__DIR__.'/../Console/stubs/'.$name.'.stub';
		$stub = File::get($stub_file);

        $search=[];
        $fields=self::fields($model);
        
        $dummy_id=$model->getRouteKeyName();
        if(is_array($dummy_id)){
        	echo ('<h3>not work with multiple keys</h3>');
        	$dummy_id=var_export($dummy_id,true);
        }
        $replace=[ 
        	'DummyNamespace' => $panel_namespace,
        	'DummyClass' => $class_name.Str::studly($name),
        	'DummyFullModel' => $class_full,
        	'dummy_id' => $dummy_id, 
        	'dummy_title' => 'title', // prendo il primo campo stringa
        	'dummy_search' => var_export($search,true),
        	'dummy_fields' => var_export($fields,true),
        	'NamespacedDummyUserModel' => 'Modules\LU\Models\User',
        	'NamespacedDummyModel'=> get_class($model),

        ];
        $stub=str_replace(array_keys($replace),array_values($replace),$stub);
        $panel_dir=$model_dir.'/'.Str::plural(Str::studly($name));
        File::makeDirectory($panel_dir, $mode = 0777, true, true);
        $panel_file=$panel_dir.'/'.$class_name.Str::studly($name).'.php';
		File::put($panel_file,$stub);
	}

	public static function fields($model){
		$fillables=$model->getFillable();
        $fields=[];
        foreach($fillables as $input_name){
        	//There is no column with name 'guid' on table 'blog_post_articles'.
        	//Doctrine \ DBAL \ Schema \ SchemaException
        	try{
            	$input_type=$model->getConnection()->getDoctrineColumn($model->getTable(),$input_name)->getType();//->getName();
            }catch(\Exception $e){
            	$input_type='Text'; 
            }
            $tmp=new \stdClass();
            $tmp->type=(string)$input_type;
            $tmp->name=$input_name;
            $fields[]=$tmp;
        }
        return $fields;
	}


	public static function updatePanel($params){
		extract($params);
		$func_file=__DIR__.'/../Console/stubs/panels/'.$func.'.stub';
		$func_stub = File::get($func_file);
		$autoloader_reflector = new \ReflectionClass($panel);
		$panel_file = $autoloader_reflector->getFileName();
		$panel_stub = File::get($panel_file);
		$panel_stub=Str::replaceLast('}',$func_stub.chr(13).chr(10).'}',$panel_stub);
		File::put($panel_file,$panel_stub);



	}
}