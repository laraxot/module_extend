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

//---- bases ----
use Modules\Xot\Providers\XotBaseServiceProvider;

class ExtendServiceProvider extends XotBaseServiceProvider{
	protected $module_dir= __DIR__;
    protected $module_ns=__NAMESPACE__;
    public $module_name='extend';

    public function bootCallback(){

        if(false){
        // --- meglio ficcare un controllo anche sull'env
        if (isset($_SERVER['SERVER_NAME']) && 'localhost' != $_SERVER['SERVER_NAME']
            && isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']
            //&& substr($_SERVER['SERVER_NAME'],0,strlen('www.'))=='www.'
        ) {
            URL::forceScheme('https');
        }
        }
       
        $this->registerTranslator();

        resolve(EngineManager::class)->extend('fulltext', function () {
            return new FullTextSearchEngine;
        });
    }  

    public function registerCallback(){
    	$this->loadHelpersFrom(__DIR__.'/../Helpers');
    }

    public function loadHelpersFrom($path){
        foreach (\glob($path.'/*.php') as $filename) {
            $filename = \str_replace('/', \DIRECTORY_SEPARATOR, $filename);
            require_once $filename;
        }
    }

    public function registerTranslator(){
         // Override the JSON Translator
        $this->app->extend('translator', function (Translator $translator) {
            $trans = new TranslatorService($translator->getLoader(), $translator->getLocale());
            $trans->setFallback($translator->getFallback());
            return $trans;
        });
    }

}