<?php
namespace Modules\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use Modules\Theme\Services\ThemeService;
//--- BASE ---
use Modules\Extend\Form\BaseFormBtnMacro;


class BtnEdit extends BaseFormBtnMacro{
	public function __invoke(){
        return function ($extra) {
            $class=__CLASS__;
            $vars=$class::before($extra);
            if($vars['error']) return $vars['error_msg'];
            return $vars['btn'];
        }; //end function
	}//end invoke
}//end class