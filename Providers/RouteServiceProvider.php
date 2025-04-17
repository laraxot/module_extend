<?php

namespace Modules\Extend\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

//--- bases ---
use Modules\Xot\Providers\XotBaseRouteServiceProvider;

class RouteServiceProvider extends XotBaseRouteServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Extend\Http\Controllers';
    protected $module_dir= __DIR__;
    protected $module_ns=__NAMESPACE__;
}
