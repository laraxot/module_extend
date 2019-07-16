<?php
namespace Modules\Extend\Services;

use Illuminate\Translation\Translator;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;



class FormService {

	public static function registerComponents(){
	    //$view_path=dirname(\View::getFinder()->find('extend::includes.components.form.text')); //devo dargli una view esistente di partenza
    	$view_path=__DIR__.'/../Resources/views/includes/components/form';
    	$blade_component='components.blade.input';
    	//if(in_admin()){  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! SOLO PER METTERE SU
    	//    $blade_component='backend::includes.'.$blade_component;
    	//}else{
        	$blade_component='pub_theme::layouts.'.$blade_component;
   		// }
    	if(!File::exists($view_path.'/_components.json')){
        	$files = File::allFiles($view_path);
        	$comps=[];
        	foreach($files as $file){
            	$filename=$file->getRelativePathname();
            	$ext='.blade.php';
            	if(ends_with($filename,$ext)){
                	$base=substr(($filename),0,-strlen($ext));
                	$name=str_replace(DIRECTORY_SEPARATOR,'_',$base);
                	$name='bs'.studly_case($name);
                	$comp_view=str_replace(DIRECTORY_SEPARATOR,'.',$base);
                	$comp_view='extend::includes.components.form.'.$comp_view;
                	$comp=new \StdClass();
                	$comp->name=$name;
                	$comp->view=$comp_view;
                	$comp->base=$base;
                	$comp->dir=$file->getPath();
                	$comps[]=$comp;
            	}//endif 
        	}//end foreach
        	File::put($view_path.'/_components.json',json_encode($comps));
    	}else{
        	$comps=File::get($view_path.'/_components.json');
        	$comps=json_decode($comps);
    	}
    	foreach($comps as $comp){
        	\Form::component($comp->name, $comp->view,
                    ['name', 'value' => null,'attributes' => [],
                    //'lang'=>$lang,
                    'comp_view'=>$comp->view,
                    'comp_dir'=>$comp->dir,
                    'blade_component'=>$blade_component]
                );
    	}//end foreach
    }//end function


    public static function registerMacros(){
        //$macros_dir=extend::getPath().'/Form/Macros';
        $macros_dir=__DIR__.'/../Form/Macros';
        Collection::make(glob($macros_dir.'/*.php'))
            ->mapWithKeys(function ($path) {
                return [$path => pathinfo($path, PATHINFO_FILENAME)];
            })
            ->reject(function ($macro) {
                return Collection::hasMacro($macro);
            })
            ->each(function ($macro, $path) {
                $class = '\\Modules\\Extend\\Form\\Macros\\'.$macro;
                //app($class)();
                //echo '<hr/>'.$class.'<br/>';
                //\Form::macro(Str::camel($macro), app($class)());
                \Form::macro('bs'.Str::studly($macro), app($class)());
                //ddd($path);
            });
    }//end function
}//end class
