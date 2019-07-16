@extends('pub_theme::layouts.app')
@section('page_heading',trans($view.'.page_heading'))
@section('content')
@include('extend::includes.components')
@include('extend::includes.flash')
@include('extend::modal_ajax')
{!! Form::bsBtnGear(['row'=>$row]) !!}
<div class="page-wrapper">
	{!! Theme::include('inner_page',['edit_type'=>$row_type],get_defined_vars() ) !!}
	{{-- // da sistemare non togliere
	{!! Theme::include('breadcrumb',[],get_defined_vars() ) !!}
	--}}
	@include('pub_theme::layouts.partials.breadcrumb')
	@include('pub_theme::layouts.partials.tabs',['tabs'=>$_panel->tabs()])
	<section class="create-page inner-page">
		<div class="container">
			<div class="row">
				@include('extend::layouts.default.common.'.$view_body)
			</div>
		</div>
	</section>
</div>
@endsection
