<?php
namespace Modules\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use Modules\Theme\Services\ThemeService;
//--- BASE ---
use Modules\Extend\Form\BaseFormBtnMacro;


class BtnGear extends BaseFormBtnMacro{
	public function __invoke(){
        return function ($extra) {
            $class=__CLASS__;
            $extra['generate_btn']=0;
            $vars=$class::before($extra);
            if($vars['error']) return $vars['error_msg'];
            $routename=Request::route()->getName();
            $routetmp=Str::before($routename,$vars['old_act_route']);
            $act='no-set';
            switch($vars['old_act_route']){
                case 'index': 
                    $act='index_edit';
                    break;
                case 'index_edit': 
                    $act='index';
                    break;
                case 'show':
                    $act='edit';
                    break;
                case 'edit':
                    $act='show';
                    break;
            }
            $routename_gear=$routetmp.$act;
            $route_params=\Route::current()->parameters();
            try{
                $route_gear=route($routename_gear,$route_params);
            }catch(\Exception $e){
                $route_gear='#';
            }
            $func=Str::camel($act);
            $row=$extra['row'];
            $btns=[];
            if(\Auth::user()->can($func,$row)){
                $tmp=[];
                $tmp['title']=$act;
                $tmp['url']=$route_gear;
                $tmp['icon']='far fa-edit';
                $tmp['modal']=0;
                $btns[]=(object)$tmp;
            }
            if(true){
                $tmp=[];
                $tmp['title']='Gestisti Traduzioni';
                $tmp['url']=route('container0.index',['container0'=>'translation','uri'=>$_SERVER['REQUEST_URI'] ]);
                $tmp['icon']='fas fa-language fa-2x';
                $tmp['modal']=1;
                $btns[]=(object)$tmp;    
            }
            $vars['btns']=$btns;
            //ddd();
            return view($vars['view_comp'],$vars);
            //return $vars['btn'];
        }; //end function
	}//end invoke
}//end class