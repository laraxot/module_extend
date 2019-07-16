<?php
namespace Modules\Extend\Traits;

//use Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
//-------- services -----
use Modules\Blog\Models\Post;
//-------- models ------
use Modules\Blog\Models\PostRelated;
//------- services -------
use Modules\Extend\Services\ThemeService;
use Modules\Extend\Services\ScoutService;
use Modules\Extend\Services\ArtisanService;

//use Modules\Extend\Services\PanelService;
//use Modules\Extend\Services\PolicyService;
use Modules\Extend\Services\StubService;


trait CrudContainerItemNoPostTrait{

	public function index(Request $request,$container,$item){
		$params = \Route::current()->parameters();
		if($container===false){
			$home_view=$params['module'].'::admin.index';
			if (\View::exists($home_view)) {
				return ThemeService::view($home_view);
			}else{
				return ThemeService::view('backend::admin.home');
			}
		}
		$container_obj=$this->getXotModel($container);
		if($item===false){
			$rows=$container_obj;
		}else{
			$types = camel_case(str_plural($container));
            $rows = $item->$types();
		}
		//$rows=$rows->unique('guid');ddd($rows);
		/*
		if (\method_exists($container_obj, 'getWith')){ 
			$with=$container_obj::getWith();
			$rows->load($with); //75, 44
		}
		*/
		$panel=StubService::getByModel($container_obj,'panel',true);
		$policy=StubService::getByModel($container_obj,'policy',true);
		$rows=$rows->with($panel->with());
		$rows=$rows->paginate(20);
		
		//ddd($rows->items());
		//ddd(ThemeService::getView());
		return ThemeService::view()
				->with('rows',$rows)
				->with('row',$container_obj)
				->with('_panel',$panel)
				;
	}

	public function edit(Request $request,$container,$item){
		$panel=StubService::getByModel($item,'panel');
		return ThemeService::view()
				->with('row',$item)
				->with('_panel',$panel)
				;
	}

	public function update(Request $request,$container,$item){
		$data = $request->all();
		$row=$item;
		$ris=$row->update($data);
		//$row->post()->save($data); //save vuole un oggetto
		if(method_exists($row,'post')){ 
			$row->post->update($data);
		}
		$this->manageRelationships(['model'=>$item,'data'=>$data,'act'=>'update']);
        \Session::flash('status', 'aggiornato! ['.$row->getKey().']!'); //.implode(',',$row->getChanges())
        //return view('xot::test'); //4 debug
        return ThemeService::action($request,$row);
	}

	/**
	* https://hackernoon.com/eloquent-relationships-cheat-sheet-5155498c209
	* https://laracasts.com/discuss/channels/eloquent/cleanest-way-to-save-model-and-relationships
	*/


	public function manageRelationships($params){
		extract($params);
		$methods=get_class_methods($model);
		$data1=collect($data)->filter(function($item,$key) use($methods){
			return (in_array($key,$methods));
		})->map(function($v,$k) use ($model){
			return (object)[
				'relationship_type'=>class_basename($model->$k()),
				'data'=>$v,
				'name'=>$k,
			];
		})->all();
		foreach($data1 as $k=>$v){
			$func=$act.'Relationships'.$v->relationship_type; //updateRelationshipsMorphOne
			$this->$func(['model'=>$model,'name'=>$v->name,'data'=>$v->data]);
		}

		if(isset($data['pivot'])){
			$func=$act.'Relationships'.'Pivot'; //updateRelationshipsMorphOne
			$this->$func(['model'=>$model,'name'=>'pivot','data'=>$data['pivot']]);	
		}
	}

	public function updateRelationshipsMorphOne($params){
		extract($params);
		$model->$name()->update($data);
	}

	public function updateRelationshipsPivot($params){
		extract($params);
		$model->$name->update($data);
	}


	public static function create(Request $request,$container,$item){
		$row=self::getXotModel($container);
		$panel=StubService::getByModel($row,'panel');
		return ThemeService::view()
			->with('row',$row)
			->with('_panel',$panel)
			;
	}

	public function store(Request $request,$container,$item){
		$data=$request->all();
		if(!isset($data['lang'])) $data['lang']=\App::getLocale(); 
		$row=$this->getXotModel($container);
		$item_new=$row->create($data);
		if($item!==false){
			$types = camel_case(str_plural($container));
			$tmp=$item->$types()->save($item_new);
		}
		if(method_exists($item_new,'post')){
			$item_new->post()->create($data);
		}
		$this->manageRelationships(['model'=>$item_new,'data'=>$data,'act'=>'store']);

		\Session::flash('status', 'aggiornato! ['.$row->getKey().']!'); //.implode(',',$row->getChanges())
        //return view('xot::test');// 4 debug
        return ThemeService::action($request,$row);
	}

	public function storeRelationshipsMorphOne($params){
		extract($params);
		if(!isset($data['lang'])) $data['lang']=\App::getLocale();
		if($model->$name()->exists()){	$model->$name()->update($data); }
		else{	$model->$name()->create($data); }
	}

	public function storeRelationshipsMorphToMany($params){
		extract($params);
		$model_linked_name=Str::singular($name);
		$model_linked=$this->getXotModel($model_linked_name);
		$row_linked=$model_linked->where($data)->first();
		ddd($row_linked);
		$model->$name()->save($row_linked);
	}
	

	

	public function show(Request $request,$container,$item){
		$panel=StubService::getByModel($item,'panel');
		return ThemeService::view()
			->with('row',$item)
			->with('_panel',$panel)
			;
	}

	public function indexEdit(Request $request,$container,$item){
		if($request->getMethod()=='POST'){
			return $this->indexUpdate($request,$container,$item);
		}
		return $this->index($request,$container,$item);
	}

	public function indexUpdate(Request $request,$container,$item){
		$data=$request->all();

		$types = camel_case(str_plural($container));
		if(isset($data[$types]['from']) || isset($data[$types]['to']) ){
			$this->saveMultiselectTwoSides($request,$container,$item);
		}


		foreach($data[$types] as $k=>$v){
			//$item->$types()->updateExistingPivot($k, $v, false);
			/*
			$tmp=$this->getXotModel($container)->find($k);
			$item->$types()->save($tmp,$v['pivot']);
			*/
 			//$item->$types()->syncWithoutDetaching([$k=>$v['pivot']]);
 			$v['pivot']['auth_user_id']=\Auth::user()->auth_user_id;
 			$item->$types()->syncWithoutDetaching([$k=>$v['pivot']]);
		}
		/*
		echo '<ol>[';
		foreach($item->$types()->get() as $k=>$v){
			echo '<li>'.$v->pivot->rating.'</li>';
		}
		echo ']</ol>';
		*/
		//return view('xot::test');
        return back()->withInput();
	}

	public function saveMultiselectTwoSides(Request $request,$container,$item){ //passo request o direttamente data ?
		$data=$request->all();
		$types = camel_case(str_plural($container));

		$items = $item->$types();
		//getPivotAccessor
		//getPivotClass
		//ddd($items->pivot());
		//ddd($items->getPivotClass());//Illuminate\Database\Eloquent\Relations\Pivot
				//Modules\LU\Models\AreaAdminArea  solo se lo ficco con lo Using 
		//ddd(get_class_methods($items));
		//ddd(class_basename($items));//BelongsToMany
		$container_obj=$this->getXotModel($container);
        $items_key = $container_obj->getKeyName();
        $items_0 = $items->get()->pluck($items_key);
        if(!isset($data[$types]['to'])){
        	$data[$types]['to']=[]; 
        }  
        $items_1 = collect($data[$types]['to']);
        $items_add = $items_1->diff($items_0);
        $items_sub = $items_0->diff($items_1);
        $items->detach($items_sub->all());
        /* da risolvere Column not found: 1054 Unknown column 'related_type' liveuser_area_admin_areas */
        try{
        	$items->attach($items_add->all(),['related_type'=>$container] ); 
        }catch(\Exception $e){
        	$items->attach($items_add->all() ); 
        }
        $status = 'collegati ['.\implode(', ', $items_add->all()).'] scollegati ['.\implode(', ', $items_sub->all()).']';
        \Session::flash('status', $status);
	}


	public static function getXotModel($name){
		$model=config('xra.model.'.$name);
		$row= new $model;
		return $row;
	}


	
	


}