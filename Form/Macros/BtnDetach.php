<?php
namespace Modules\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Collective\Html\FormFacade as Form;
//----- services -----
use Modules\Theme\Services\ThemeService;
//--- BASE ---
use Modules\Extend\Form\BaseFormBtnMacro;


class BtnDetach extends BaseFormBtnMacro{

	

	public function __invoke(){
		return function ($extra) {
			$class=__CLASS__;
            $vars=$class::before($extra);
            if($vars['error']) return $vars['error_msg'];
			
  			//btn($extra);
			//$this->init($extra);
			$routename=Request::route()->getName();
			$routename=str_replace('.index', '.destroy',$routename );
			extract($extra);
			/*
			if (!$user->can('detach', $row)){
                return '[not can detach]['.get_class($row).']';
            }
            */
			//-----------
			$id=$row->getKey();
			$route=$row->detach_url;
			if($route==''){
				$params=\Route::current()->parameters();
				$params=array_merge($params, $extra);
				$params['routename']='';unset($params['routename']);
				$route=route($routename, $params);
			}
			/* --- sweetalert e btndelete messi nel webpack
			ThemeService::add('theme/bc/sweetalert2/dist/sweetalert2.min.js');
			ThemeService::add('theme/bc/sweetalert2/dist/sweetalert2.min.css');
			ThemeService::add('extend::js/btnDeleteX2.js');
			*/
			$class='btn btn-small btn-danger';
			if (isset($extra['class'])) {
				$class.=' '.$extra['class'];
			}  
			return '<a class="'.$class.'" href="#" data-token="'. csrf_token() .'" data-id="'.$id.'" data-href="'.$route.'?id='.$id.'" data-toggle="tooltip" title="Detach">
				<i class="fa fa-unlink fa-fw" aria-hidden="true"></i>'.$route.'
			</a>';
			

		}; //end function
	}//end invoke
}//end class