@extends('adm_theme::layouts.app')
@section('content')
@php
	//echo '['.Theme::getView().']';
	//$tabs=$row->tabs;
	//$parent_tabs=$row->parent_tabs;
	//ddd($params);
@endphp
	@include('extend::includes.components')
	@include('extend::includes.flash')
	@include('extend::modal_ajax')
	[[ {{ Theme::getView() }} ]]
	@includeFirst([$view_default.'.btns.gear',$view_extend.'.btns.gear'])
		<div class="page-wrapper">
			@if(isset($step))
				@include('pub_theme::layouts.partials.top_links',['step'=>$step])
			@endif
			{{--
			@if(is_array($parent_tabs) && is_object($second_last))
				@includeFirst(
					[
						'pub_theme::layouts.default.show.inner_page.'.$parent_type,
						'pub_theme::layouts.default.show.inner_page',
						'extend::layouts.default.show.inner_page',
					]
				)
				
			@else
				{!! Theme::include('inner_page',[],get_defined_vars() ) !!}
			@endif
			{!! Theme::include('breadcrumb',[],get_defined_vars() ) !!}
			--}}
			@include('adm_theme::layouts.partials.breadcrumb_container_item')
			{{--
			{!! Theme::include('parent_tabs',['tabs'=>$parent_tabs],get_defined_vars() ) !!}
			{!! Theme::include('tabs',['tabs'=>$tabs],get_defined_vars() ) !!}
			--}}
			@include('adm_theme::layouts.partials.tabs',['tabs'=>$_panel->tabs()])
			<section class="restaurants-page">
				<div class="container">
					<div class="row">
						{!! Theme::include($view_body,[],get_defined_vars() ) !!}
					</div>
				</div>
			</section>
		</div>
@endsection		
