@php
	if(!isset($view)){
		$view=$comp_view;
	}
	$view_noact=implode('.',array_slice(explode('.',$view),0,-1));
	$label=isset($attributes['label'])?$attributes['label']:trans($view_noact.'.field.'.$name);
	$placeholder=trans($view_noact.'.field.'.$name.'_placeholder');
@endphp
{{-- [{{  \Route::currentRouteName() }}] container0.create --}}
{{-- {{ $view_name }} extend::includes.components.form.text --}}
{{--{{ $view }}--}}
@component($blade_component,compact('name','value','attributes','comp_view'))
	@slot('label')
		{{ Form::label($name, $label , ['class' => 'control-label']) }}
	@endslot
	@slot('input')
		{{ Form::text($name, $value, array_merge(['class' => 'form-control','placeholder'=>$placeholder], $attributes)) }}
		@if ( $errors->has($name) )
			<span class="help-block">
				<strong>{{ $errors->first($name) }}</strong>
			</span>
		@endif
	@endslot
@endcomponent