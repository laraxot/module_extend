<?php
namespace Modules\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use Modules\Extend\Services\ThemeService;


class BtnEdit{
	public function __invoke(){
        return function ($extra, $from='index', $to='edit') {
            $user=\Auth::user();
            if($user==null) return '';
            extract($extra);
            if (!$user->can('edit', $row)){
                return '[not can edit '.get_class($row).']';
            }
            //return '['.$row->title.']['.isset($row->pivot).']';
            //$route=$row->edit_url;
            $route=null;
            if($route==null){
                $params=\Route::current()->parameters();
                $params=array_merge($params, $extra);
                if(0){ //solo per ricordarmi esistenza
                    list($containers,$items)=params2ContainerItem($params);
                    $params['item'.count($items)]=$extra['row'];
                }
                if(!isset($params['lang'])){ $params['lang']=\App::getLocale(); }
                $routename=Request::route()->getName();
                $routename=str_replace('.index_edit', '.index',$routename );
                $routename=str_replace('.'.$from, '.'.$to, $routename);
                //echo '<h3>'.$routename.'</h3>';
                //$params['item0']=$extra['row'];
                //ddd($params);
                //admin/{module}/{lang}/{container0}/{item0}/edit
                $route=route($routename, $params);
            }
            $class='btn btn-small btn-info';
            if (isset($extra['class'])) {
                $class.=' '.$extra['class'];
            }
            $params=\Route::current()->parameters();
            extract($params);
            //ddd($container0->routeKey());
            //$class0=get_class($container0);
            //ddd($class0);

            //$params['container0']=collect(config('xra.model'))->search($class0); 
            //ddd($params['container0']); 
            //ddd(route('container0.index',$params));
            return '<a class="'.$class.'" href="'.$route.'" data-toggle="tooltip" title="Modifica">
            <i class="fa fa-pencil fa-fw far fa-edit" aria-hidden="true"></i></a>';
        }; //end function
	}//end invoke
}//end class