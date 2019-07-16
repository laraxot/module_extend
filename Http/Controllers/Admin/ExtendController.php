<?php



namespace Modules\Extend\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Extend\Services\ThemeService;
//--- services
use Modules\Extend\Traits\ArtisanTrait;

class ExtendController extends Controller
{
    public function index(Request $request)
    {
        if ($request->act=='routelist') {
            return ArtisanTrait::exe('route:list');
        }

        return ThemeService::view();
    }

    //end function
 //
}//end class
