<?php
namespace Modules\Extend\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
//---------------
use Illuminate\Database\Eloquent\Relations\Relation; // per dizionario morph 

use Illuminate\Translation\Translator;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;



use Laravel\Scout\EngineManager; // per slegarmi da tntsearch
//use Modules\XRA\Services\CustomInputService;
use Modules\Extend\Engines\FullTextSearchEngine;
 
use Modules\Extend\Services\FormService;
use Modules\Extend\Services\TranslatorService;

class ExtendServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {

        
        $this->registerTranslations();
        $this->registerTranslator(); //!!! qui registro le traduzioni delle view ..
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        //------------------ LARAXOT
        if (isset($_SERVER['SERVER_NAME']) && 'localhost' != $_SERVER['SERVER_NAME']
            && isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']
            //&& substr($_SERVER['SERVER_NAME'],0,strlen('www.'))=='www.'
        ) {
            URL::forceScheme('https');
        }

        resolve(EngineManager::class)->extend('fulltext', function () {
            return new FullTextSearchEngine;
        });



        $this->mergeConfigs();
        //$this->registerPackages();
        $map=config('xra.model');
        Relation::morphMap($map);

        $adm_theme = config('xra.adm_theme');
        $adm_theme_dir = public_path('themes'.\DIRECTORY_SEPARATOR.$adm_theme);
        $pub_theme = config('xra.pub_theme');
        $pub_theme_dir = public_path('themes'.\DIRECTORY_SEPARATOR.$pub_theme);
        //die($pub_theme_dir.'['.__LINE__.']['.__FILE__.']');

        $this->app['view']->addNamespace('adm_theme', $adm_theme_dir);
        $this->app['view']->addNamespace('pub_theme', $pub_theme_dir);

        //ddd($pub_theme_dir.'/translations');
        $this->loadTranslationsFrom($pub_theme_dir.'/translations', 'pub_theme');
        $this->loadTranslationsFrom($adm_theme_dir.'/translations', 'adm_theme');
        
        FormService::registerComponents();
        FormService::registerMacros();
        \View::composer('*', function($view){
            \View::share('view_name', $view->getName());
        });
    }

    public function mergeConfigs(){
        if (!isset($_SERVER['SERVER_NAME']) || '127.0.0.1' == $_SERVER['SERVER_NAME']) {
            $_SERVER['SERVER_NAME'] = 'localhost';
        }
        $server_name = str_slug(\str_replace('www.', '', $_SERVER['SERVER_NAME']));
        if(!\File::exists(base_path('config/'.$server_name))){
            $server_name = 'localhost';
        }
        $configs=['database','filesystems','auth','metatag','services','xra']; //auth sarebbe da spostare in LU,metatag in extend
        foreach($configs as $v){
            $extra_conf=config($server_name.'.'.$v);
            $original_conf=config($v);
            if(!is_array($original_conf)) $original_conf=[];
            if(!is_array($extra_conf)) $extra_conf=[];
            $merge_conf=array_merge($original_conf,$extra_conf); //_recursive
            \Config::set($v, $merge_conf);
        }
        //ddd(config('database')); //4debug
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        /*
        $rc = new \ReflectionClass(get_class($this));
        $dir = \dirname($rc->getFileName());
        dd($dir);//C:\xampp\htdocs\lara\foodm\Modules\Extend\Providers
        dd(__DIR__);//C:\xampp\htdocs\lara\foodm\Modules\Extend\Providers
        */

        $this->loadHelpersFrom(__DIR__.'/../Helpers');
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('extend.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'extend'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {   
        $sourcePath = realpath(__DIR__.'/../Resources/views');
        /*
        $viewPath = resource_path('views/modules/extend');


        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/extend';
        }, \Config::get('view.paths')), [$sourcePath]), 'extend');
        */
        $this->loadViewsFrom($sourcePath,'extend');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {   
        /*
        $langPath = resource_path('lang/modules/extend');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'extend');
        } else {
        }*/
        $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'extend');
    }

    //--------------------------
    public function registerTranslator(){
         // Override the JSON Translator
        $this->app->extend('translator', function (Translator $translator) {
            $trans = new TranslatorService($translator->getLoader(), $translator->getLocale());
            $trans->setFallback($translator->getFallback());
            return $trans;
        });
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }


    public function loadHelpersFrom($path){
        foreach (\glob($path.'/*.php') as $filename) {
            $filename = \str_replace('/', \DIRECTORY_SEPARATOR, $filename);
            require_once $filename;
        }
    }


}
