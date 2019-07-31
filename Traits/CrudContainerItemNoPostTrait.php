<?php
namespace Modules\Extend\Traits;

//use Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Builder; //per il w

//-------- services -----
use Modules\Blog\Models\Post;
//-------- models ------
use Modules\Blog\Models\PostRelated;
//------- services -------
use Modules\Theme\Services\ThemeService;
use Modules\Extend\Services\ScoutService;
use Modules\Extend\Services\ArtisanService;

//use Modules\Extend\Services\PanelService;
//use Modules\Extend\Services\PolicyService;
use Modules\Extend\Services\StubService;


trait CrudContainerItemNoPostTrait{

	public static function index(Request $request,$container,$item){

		$params = \Route::current()->parameters();
		if($container===false){
			$home_view=$params['module'].'::admin.index';
			if (\View::exists($home_view)) {
				return ThemeService::view($home_view);
			}else{
				return ThemeService::view('backend::admin.home');
			}
		}
		if($item===false){
			//$row=$this->getXotModel($container);
			$row=self::getXotModel($container);
			$rows=$row;
		}else{
			$types = camel_case(str_plural($container));
            $rows = $item->$types();
            //ddd($rows);

            //$pivot_class=$rows->getPivotClass();
			//$foreign_pivot_key_name=$rows->getForeignPivotKeyName();
			//$pivot=new $pivot_class;
			//$panel=StubService::getByModel($pivot,'panel',true);
			//$panel->foreign_pivot_key_name=$foreign_pivot_key_name;
			$row=$rows->getRelated(); //???? 
		}
		/*
		if($request->ajax() && $request->has('query') ){
			$lang=\App::getLocale();
			$q=$request->input('query');
			$rows=Post::select('post_id as id','title as label')
						->where('title','like','%'.$q.'%')
						->where('post_type','cuisine_cat')
						->where('lang',$lang)
						->limit(10)
						->get()
						->toJson();
			die($rows);
		}
		*/
		
		$panel=StubService::getByModel($row,'panel',true);
		$policy=StubService::getByModel($row,'policy',true);
		//$rows=$rows->with($panel->with());
		///------- search test 
		/*
		$rows = $rows->whereHas('post', function (Builder $query) {
    		$query->where('title', 'like', '9%');
		});
		ddd($panel->search());
		fields : funzione
		with : funzione
		search : ??
		*/
		//--------------------------------
		//$rows=$panel->indexQuery($request,$rows);
		$rows=$panel->indexRows($request,$rows);
		if($panel->force_exit){
			return $panel->out;
		}



		$rows=$rows->paginate(20);
		
		
		return ThemeService::view()
				->with('rows',$rows)
				->with('row',$row)
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
			if($row->post==null){
				$row->post()->create($data);
			}else{
				$row->post->update($data);
			}
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


	public static function manageRelationships($params){
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
			//$this->$func(['model'=>$model,'name'=>$v->name,'data'=>$v->data]);
			self::$func(['model'=>$model,'name'=>$v->name,'data'=>$v->data]);
		}

		if(isset($data['pivot'])){
			$func=$act.'Relationships'.'Pivot'; //updateRelationshipsMorphOne
			//$this->$func(['model'=>$model,'name'=>'pivot','data'=>$data['pivot']]);	
			//self::$func(['model'=>$model,'name'=>'pivot','data'=>$data['pivot']]);
			$params['name']='pivot';
			$params['data']=$data['pivot'];
			self::$func($params);		
		}
	}

	public static function updateRelationshipsMorphOne($params){
		extract($params);
		$model->$name()->update($data);
	}

	public static function updateRelationshipsMorphToMany($params){
		extract($params);
		//$res=$model->$name()->syncWithoutDetaching($data);
		foreach($data as $k => $v){
			$res=$model->$name()->syncWithoutDetaching([$k=>$v['pivot']]);
			$model->$name()->touch(); 
		}

		//ddd($res);
		/*"attached" => []
  "detached" => []
  "updated" => array:2 [▼
    0 => 2
    1 => 4
    //*/
	}

	public static function updateRelationshipsPivot($params){
		extract($params);
		$model->$name->update($data);
	}

	public static function storeRelationshipsPivot($params){
		/*
		extract($params);
		$types=Str::plural($container);
		//ddd($params);
		//$model->$name()->create($data);
		$k=$model->getKey();
		$res=$item->$types()->update($model,$data);
		//ddd($res);
		*/
	}
 

	public static function create(Request $request,$container,$item){
		$row=self::getXotModel($container);
		$panel=StubService::getByModel($row,'panel');
		return ThemeService::view()
			->with('row',$row)
			->with('_panel',$panel)
			;
	}

	public static function store(Request $request,$container,$item){
		$data=$request->all();
		if(!isset($data['lang'])) $data['lang']=\App::getLocale();
		$types = camel_case(str_plural($container));
		if(is_object($item)){ //l'oggetto figlio potrebbe avere un modello diverso
			$row=$item->$types()->getRelated();
		}else{ 
			//$row=$this->getXotModel($container); 
			$row=xotModel($container);
		}
		
		$item_new=$row->create($data);
		
		if($item!==false){
			if(!is_object($item)){ ddd($item); };
			$pivot_data=[];
			if(isset($data['pivot'])){ $pivot_data=$data['pivot']; }
			$tmp=$item->$types()->save($item_new,$pivot_data);
		}
		//*
		if(method_exists($item_new,'post')){ // mi evita delle query dopo
			if(isset($data['post'])){
				$data=array_merge($data,$data['post']); //forzatura
			}
			$item_new->post()->create($data);
		}
		//*/
		self::manageRelationships(['model'=>$item_new,'data'=>$data,'act'=>'store','container'=>$container,'item'=>$item,]);

		\Session::flash('status', 'aggiornato! ['.$row->getKey().']!'); //.implode(',',$row->getChanges())
        //return view('xot::test');// 4 debug
        return ThemeService::action($request,$row);
	}

	public static function storeRelationshipsMorphOne($params){
		extract($params);
		if(!isset($data['lang'])) $data['lang']=\App::getLocale();
		if($model->$name()->exists()){	$model->$name()->update($data); }
		else{	$model->$name()->create($data); }
	}

	public static function storeRelationshipsMorphToMany($params){
		extract($params);
		/*
			name= nome relazione
		*/
		/*	
		ddd($params);	
		$model_linked_name=Str::singular($name);
		$model_linked=$this->getXotModel($model_linked_name);
		$row_linked=$model_linked->where($data)->first();
		ddd($row_linked);
		$model->$name()->save($row_linked);
		*/
		foreach($data as $k => $v){
			if(!isset($v['pivot']['auth_user_id'])){
				$v['pivot']['auth_user_id']=\Auth::user()->auth_user_id;
			}
			$model->$name()->syncWithoutDetaching([$k=>$v['pivot']]);
		}
	}
	

	

	public static function show(Request $request,$container,$item){
		$panel=StubService::getByModel($item,'panel');
		return ThemeService::view()
			->with('row',$item)
			->with('_panel',$panel)
			;
	}
	public  function indexAttach(Request $request,$container,$item){
		if($request->getMethod()=='POST'){
			return $this->indexAttachSave($request,$container,$item);
		}
		$types = camel_case(str_plural($container));
		if(is_object($item)){
			$rows=$item->$types();
			$pivot_class=$rows->getPivotClass();
			$pivot=new $pivot_class;
			$panel=StubService::getByModel($pivot,'panel',true);
			$panel->pivot_key_names=[];
			$panel->pivot_key_names[]=$rows->getForeignPivotKeyName();
			if(method_exists($rows, 'getMorphType')){
				$panel->pivot_key_names[]=$rows->getMorphType();
			}
			///*
			return ThemeService::view()
				->with('row',$pivot)
				->with('_panel',$panel)
			;
		}
		//return $this->create($request,$container,$item); //crea collegamento .. 
	}

	public static function indexAttachSave(Request $request,$container,$item){
		$data=$request->all();
		$types = camel_case(str_plural($container));
		$related_pivot_key_name=$item->$types()->getRelatedPivotKeyName();
		$related_pivot_key_value=$data[$related_pivot_key_name];
		$rows=$item->$types()->attach($related_pivot_key_value,$data); //forse dovremmo aggiungere il tipo di relazione
		//ddd($data);
		/*
		if(is_object($item)){
			$rows=$item->$types();
			$pivot_class=$rows->getPivotClass();
			$foreign_pivot_key_name=$rows->getForeignPivotKeyName();
			$pivot=new $pivot_class;
			$pivot->
		};
		ddd('senza item ?');
		*/
		//return view('xot::test');// 4 debug
		return ThemeService::action($request,$item);
	}


	public static function indexEdit(Request $request,$container,$item){
		if($request->getMethod()=='POST'){
			return $this->indexUpdate($request,$container,$item);
		}
		//return $this->index($request,$container,$item);
		return self::index($request,$container,$item);
	}

	public static function indexUpdate(Request $request,$container,$item){
		$data=$request->all();

		$types = camel_case(str_plural($container));
		if(isset($data[$types]['from']) || isset($data[$types]['to']) ){
			$this->saveMultiselectTwoSides($request,$container,$item);
		}
		$this->manageRelationships(['model'=>$item,'data'=>$data,'act'=>'indexUpdate']);
		//ddd($data);

		//foreach($data[$types] as $k=>$v){
			//$item->$types()->updateExistingPivot($k, $v, false);
			/*
			$tmp=$this->getXotModel($container)->find($k);
			$item->$types()->save($tmp,$v['pivot']);
			*/
 			//$item->$types()->syncWithoutDetaching([$k=>$v['pivot']]);
 			//$v['pivot']['auth_user_id']=\Auth::user()->auth_user_id;
 			//$item->$types()->syncWithoutDetaching([$k=>$v['pivot']]);
		//}
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

	public static function indexUpdateRelationshipsMorphToMany($params){
		extract($params);
		///*
		$auth_user_id=\Auth::user()->auth_user_id;
		$data=collect($data)->map(function($item) use($auth_user_id){
			$item['auth_user_id']=$auth_user_id;
			return $item;
		})->all();
		//*/
		$res=$model->$name()->syncWithoutDetaching($data);
	}

	public static function saveMultiselectTwoSides(Request $request,$container,$item){ //passo request o direttamente data ?
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

	//*
	public static function getXotModel($name){ //spostare in helper ?
		$model=tenantConfig('xra.model.'.$name);
		//ddd($model);
		if($model==null){
			echo('<h3>not exists ['.$name.'] on config xra.model</h3>');
			ddd(config('xra.model'));
		}
		$row= new $model;
		return $row;
	}
	//*/

	public static function detach(Request $request,$container,$item){
		$item->pivot->delete();// da aggiungere pivot_id
		$status='scollegato';
		\Session::flash('status', $status);
	}//end detach
	
	


}//end class