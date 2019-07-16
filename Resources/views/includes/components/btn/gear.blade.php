<div class="color-palate">
	<div class="color-trigger">
		{{--  
		<i class="fa fa-gear"></i>
		--}}
		<i class="fas fa-cog"></i>
	</div>
	<div class="color-palate-head">
		<h6>@lang('manage')</h6>
	</div>
	<br>
	<div class="palate-foo">
		{{--  
		@if(!isset($params['container1']))
		<a href="{{ route('container0.show',$params) }}" class="btn theme-btn" >
		@lang($view_default.'.view_page')<i class="fa fa-show"></i>
		</a>
		@else
		<a href="{{ route('container0.container1.show',$params) }}" class="btn theme-btn" >
		@lang($view_default.'.view_page')<i class="fa fa-show"></i>
		</a>
		@endif
		@include('pub_theme::layouts.partials.btns.translate_manager')
		--}}
		@php
			//$url_t=route('translation.index',array_merge($params,['uri'=>$_SERVER['REQUEST_URI']]));
			$url_t=route('container0.index',['container0'=>'translation','uri'=>$_SERVER['REQUEST_URI'] ]);
		@endphp
		
		<button type="button" class="btn btn-default" data-toggle="modal" data-target="#myModalAjax" data-title="languages" data-href="{{ $url_t }}">
			<i class="fas fa-language fa-3x" aria-hidden="true"></i> Gestisti Traduzioni
		</button>


	</div>
</div>
