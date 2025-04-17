<?php
namespace Modules\Extend\Form;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Collective\Html\FormFacade as Form;
//----- services -----
use Modules\Theme\Services\ThemeService;

abstract class BaseFormBtnMacro{
	
	public static function before($params){
        $generate_btn=1;   
		$user=\Auth::user(); 
		if($user==null) return ['error'=>1,'error_msg'=>'[Not Logged]'];
		$class=get_called_class();
		$class_name=class_basename($class);//BtnDetach
		$btn=Str::after($class_name,'Btn');
		if($btn=='Delete') $btn='Destroy';
		$act=Str::camel($btn);
		extract($params);
		if (!$user->can($act, $row) && !in_array($act,['gear']) ) {
			$error_msg='[not can '.$act.']['.get_class($row).']';
            return ['error'=>1,'error_msg'=>$error_msg];
        }
        if(!isset($row->pivot) && $act=='detach'){
        	return ['error'=>1,'error_msg'=>'[Not Pivot]'];
        }
        $act_route=Str::snake($act);
        $mutator=$act_route.'_url';

        $route=$row->$mutator;
        $route_action = \Route::currentRouteAction();
		$old_act=Str::snake(Str::after($route_action,'@'));
        $routename=Request::route()->getName();
        $old_act_route=last(explode('.',$routename));
        
        $routename_act=Str::before($routename,$old_act_route).''.$act_route;
		$route_params=\Route::current()->parameters();
        if(\Route::has($routename_act)){
            $route=route($routename_act,array_merge($route_params,[$row]));
        }else{
            $route='#'.$routename_act;
        }
        $view_comp_dir='extend::includes.components.btn';
        $view_comp=$view_comp_dir.'.'.$act_route;
        $data=[
        	'error'=>0,
        	'error_msg'=>'no errors',
        	'user'=>$user,
        	'class_name'=>$class_name,
        	'btn'=>$btn,
        	'act'=>$act,
        	'act_route'=>$act_route,
        	'old_act'=>$old_act,
        	'old_act_route' => $old_act_route,
        	'row'=>$row,
        	'id'=>2,
        	'routename_act'=>$routename_act,
        	'route'=>$route,
        	'btn_class'=>'btn btn-small btn-info',
            'view_comp'=>$view_comp,
            'view_comp_dir'=>$view_comp_dir,
        ];
        //ddd($generate_btn);
        if($generate_btn){
            $data['btn']=view($view_comp,$data);
        }
        return $data;
    }
}