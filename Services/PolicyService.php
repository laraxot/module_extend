<?php
namespace Modules\Extend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class PolicyService{

	public static function getByModel($model){
		$class_full=get_class($model);
		$class_name=class_basename($model);
		$class=Str::before($class_full,$class_name);
		$policy=$class.'Policies\\'.$class_name.'Policy';
		//ddd($policy);//  =  Modules\LU\Models\Policies\UserPolicy
		if(class_exists($policy)){
			/*
			if(!method_exists($policy, 'tabs')){
				self::updatePolicy(['Policy'=>$policy,'func'=>'tabs']);
			}
			*/

			return new $policy;
		}
		self::createPolicy($model);
		\Session::flash('status', 'Policy created');
		return redirect()->back();
	}

	public static function createPolicy($model){
		$class_full=get_class($model);
		$class_name=class_basename($model);
		$class=Str::before($class_full,$class_name);
		$policy_namespace=$class.'Policies';
		$policy=$policy_namespace.'\\'.$class_name.'Policy';
		//---- creazione Policy
		$autoloader_reflector = new \ReflectionClass($model);
		$class_file_nanme = $autoloader_reflector->getFileName();
		$model_dir=dirname($class_file_nanme);// /home/vagrant/code/htdocs/lara/multi/laravel/Modules/LU/Models
		$stub_file=__DIR__.'/../Console/stubs/policy.stub';
		$stub = File::get($stub_file);
        $search=[];
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
        $dummy_id=$model->getRouteKeyName();
        if(is_array($dummy_id)){
        	echo ('<h3>not work with multiple keys</h3>');
        	$dummy_id=var_export($dummy_id,true);
        }
        $replace=[ 
        	'DummyNamespace' => $policy_namespace,
        	'DummyClass' => $class_name.'Policy',
        	'DummyFullModel' => $class_full,
        	'dummy_id' => $dummy_id, 
        	'dummy_title' => 'title', // prendo il primo campo stringa
        	'dummy_search' => var_export($search,true),
        	'dummy_fields' => var_export($fields,true),

        ];
        $stub=str_replace(array_keys($replace),array_values($replace),$stub);

        ddd($stub);

        $policy_dir=$model_dir.'/Policies';
        File::makeDirectory($policy_dir, $mode = 0777, true, true);
        $policy_file=$policy_dir.'/'.$class_name.'Policy.php';
		File::put($policy_file,$stub);
	}


	public static function updatePolicy($params){
		extract($params);
		$func_file=__DIR__.'/../Console/stubs/Policies/'.$func.'.stub';
		$func_stub = File::get($func_file);
		$autoloader_reflector = new \ReflectionClass($policy);
		$policy_file = $autoloader_reflector->getFileName();
		$policy_stub = File::get($policy_file);
		$policy_stub=Str::replaceLast('}',$func_stub.chr(13).chr(10).'}',$policy_stub);
		File::put($policy_file,$policy_stub);



	}
}