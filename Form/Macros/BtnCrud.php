<?php
namespace Modules\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use Modules\Extend\Services\ThemeService;


class BtnCrud{
	public function __invoke(){
        return function ($extra) {
    		$btns='';
    		$btns.=Form::bsBtnEdit($extra);
    		$btns.=Form::bsBtnDelete($extra);
    		$btns.=Form::bsBtnDetach($extra);
    		return $btns;
		}; //end function
	}//end invoke
}//end class