<?php
namespace Modules\Extend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class StubService{

	public static function missingClass($params){
		extract($params);
		$class_name=class_basename($class);
		$class_ns=substr($class, 0,-(strlen($class_name)+1));
		$params['class_name']=$class_name;
		$params['namespace']=$class_ns;
		//$params['dir']=realpath(__DIR__.'/../..');
		$autoloader_reflector = new \ReflectionClass($model);
		$class_file_name = $autoloader_reflector->getFileName();
		$dir=dirname($class_file_name);
		$params['dir']=$dir;
		$params['dummy_id']=$model->getRouteKeyName();
		$params['search']=[];
		$params['fields']=[];
		//ddd($params);
		$stub_name=$stub;
		$stub_file=__DIR__.'/../Console/stubs/'.$stub_name.'.stub';
		$stub = File::get($stub_file);
		$replace=self::replaces($params);
		$stub=str_replace(array_keys($replace),array_values($replace),$stub);
		switch($stub_name){
			case 'migration_morph_pivot':
				$file=$dir.'/../Database/Migrations/'.date('Y_m_d_Hi00').'_create_'.Str::snake($class_name).'_table.php';
			break;
			case 'morph_pivot':
				$file=$dir.'/'.$class_name.'.php';
				self::missingClass([
                	'class'=>$class,
                	'stub'=>'migration_morph_pivot',
                	'model'=>$model,
            	]);
			break;
			default:
				ddd('['.$stub_name.'] Unkwonn !');
			break;
		}

		//ddd($file);

		//ddd($stub);
		//ddd($autoloader_reflector);
		//ddd($params);
		//
		File::put($file,$stub);
		$msg=(' ['.$class.'] is under creating , refresh page');

		


		\Session::flash($msg);
	}

	public static function getByModel($model,$name,$create = false){
		if(!is_object($model)){
			echo '<h3>Model: ['.$model.']</h3>';
			$params = \Route::current()->parameters();
			return null;
		}
		$class_full=get_class($model);
		$class_name=class_basename($model);
		//$class=Str::before($class_full,$class_name);
		$class=substr($class_full, 0,-strlen($class_name));
		$panel=$class.Str::plural(Str::studly($name)).'\\'.$class_name.Str::studly($name);
		try{
			if(class_exists($panel)){
				return new $panel;
			}
		}catch(\Exception $e){
			ddd($e);
		}
		if(!$create){
			ddd($panel.' NOT EXISTS !');
		}else{
			/* -- 4 debug 
			$t= new $panel;
			ddd($t);
			ddd('devo creare '.$panel);
			*/
		}
		self::create($model,$name);
		//return new $panel;
		ddd($name.' ['.$class_full.']['.$panel.'] is under creating , refresh page');
		\Session::flash('status', $name.' created');
		//return redirect()->back();
	}

	public static function replaces($params){
		extract($params);
		$replaces=[ 
        	'DummyNamespace' => $namespace, 
        	'DummyClass' => $class_name,
        	'DummyFullModel' => $class,
        	'dummy_id' => $dummy_id, 
        	'dummy_title' => 'title', // prendo il primo campo stringa
        	'dummy_search' => var_export($search,true),
        	'dummy_fields' => var_export($fields,true),
        	'NamespacedDummyUserModel' => 'Modules\LU\Models\User',
        	'NamespacedDummyModel'=> get_class($model),

        ];
        return $replaces;
	}

	public static function create($model,$name){
		$class_full=get_class($model);
		$class_name=class_basename($model);
		//$class=Str::before($class_full,$class_name);
		$class=substr($class_full, 0,-strlen($class_name));
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
        $stub=str_replace('stdClass::__set_state','(object)',$stub);
        //$stub=str_replace('  ','    ',$stub);
        //$stub=str_replace(chr(13),chr(13).'    ',$stub);
        //$stub=str_replace(chr(10),chr(10).'    ',$stub);


        $panel_dir=$model_dir.'/'.Str::plural(Str::studly($name));
        File::makeDirectory($panel_dir, $mode = 0777, true, true);
        $panel_file=$panel_dir.'/'.$class_name.Str::studly($name).'.php';
        if(!File::exists($panel_file)){
			File::put($panel_file,$stub);
		}else{
			echo('<h3>['.$panel_file.'] Just exists</h3>');
			ddd(debug_backtrace());
		}
	}

	public static function fields($model){
		$fillables=$model->getFillable();
		if(count($fillables)==0){

			$fillables=$model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
			$fillables=collect($fillables)->except([
				'created_at','updated_at','updated_by','created_by','deleted_at','deleted_by',
				'deleted_ip','created_ip','updated_ip'])->all();
			//errato..
			$autoloader_reflector = new \ReflectionClass($model);
			$class_filename = $autoloader_reflector->getFileName();
			$fillables_str=chr(13).chr(10).'    protected $fillable=[\''.implode("','",$fillables)."'];".chr(13).chr(10);
			$class_content= File::get($class_filename);
			$class_content_before=Str::before($class_content,'{');
			$class_content_after=Str::after($class_content,'{');
			$class_content_new=$class_content_before.'{'.$fillables_str.$class_content_after;
			//ddd($class_content_new);
			//ddd($fillables_str);
			//ddd($class_filename);
			//ddd('there are not fillable on ['.get_class($model).']');
			File::put($class_filename,$class_content_new);
		}
        $fields=[];
        foreach($fillables as $input_name){
        	//There is no column with name 'guid' on table 'blog_post_articles'.
        	//Doctrine \ DBAL \ Schema \ SchemaException
            $tmp=new \stdClass();
        	try{
            	$col=$model->getConnection()->getDoctrineColumn($model->getTable(),$input_name);//->getType();//->getName();
            	if($col->getAutoincrement()){
            		$tmp->type='Id';
            	}else{
            		$tmp->type=(string)$col->getType();
            	}
            	$tmp->name=$input_name;
            	if($col->getNotnull()){
            		$tmp->rules='required';
            	}
            	$tmp->comment=$col->getComment();

            }catch(\Exception $e){
                //$input_type='Text'; 
                //$tmp=new \stdClass();
                $tmp->type='Text';
                $tmp->name=$input_name;
                $tmp->comment='not in Doctrine';
            }

            $fields[]=$tmp;
            //debug_getter_obj(['obj'=>$col]);
            /*
             #_type: IntegerType {#983}
  #_length: null
  #_precision: 10
  #_scale: 0
  #_unsigned: true
  #_fixed: false
  #_notnull: true
  #_default: null
  #_autoincrement: true
  #_platformOptions: []
  #_columnDefinition: null
  #_comment: null
  #_customSchemaOptions: []
  #_name: "id"
  #_namespace: null
  #_quoted: false
  		*/

            /*
            $tmp=new \stdClass();
            $tmp->type=(string)$input_type;
            $tmp->name=$input_name;
            $fields[]=$tmp;
            */
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