<?php
namespace Modules\Extend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use File;
//---- services --
use Modules\Theme\Services\ThemeService;
use Modules\Extend\Services\TranslatorService;

class TranslationController extends Controller{

	public function index(Request $request){
		return ThemeService::view();
	}


	public function store(Request $request){
        $data=$request->all();
        $trans=$data['trans'];
        TranslatorService::store($trans);
        if (\Request::ajax()) {
                $response = [
                    'success' => true,
                    //'data'    => $result,
                    'message' => 'OK',
                ];
                $response = \array_merge($data, $response);
                return response()->json($response, 200);
        }
        return redirect()->back();
        
    }
}