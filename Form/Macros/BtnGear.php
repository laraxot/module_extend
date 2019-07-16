<?php
namespace Modules\Extend\Form\Macros;
use Illuminate\Support\Facades\Request;
//use Illuminate\Http\Request;
//use Request;

use Collective\Html\FormFacade as Form;
//----- services -----
use Modules\Extend\Services\ThemeService;


class BtnGear{
	public function __invoke(){
        return function ($extra) {
        	extract($extra);
        	ThemeService::add('extend::js/gear.js');
			ThemeService::add('extend::css/gear.css');
			$view='extend::includes.components.btn.gear';
			return view($view);
        };
    }
}