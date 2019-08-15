@php
	$view_noact=implode('.',array_slice(explode('.',$view),0,-1));
	$label=isset($attributes['label'])?$attributes['label']:trans($view_noact.'.field.'.$name);
	$placeholder=trans($view_noact.'.field.'.$name.'_placeholder');
@endphp
<div class="form-group{{ $errors->has($name) ? ' has-error' : '' }}">
	{{ Form::label($name,  $label, ['class' => 'col-md-4 control-label']) }}
	<div class="col-md-6">
		<div class="datepicker-input input-group date">
		{{ Form::number($name, $value, array_merge(['class' => 'form-control'], $attributes)) }}
		<span class="input-group-addon">
            <span {{--class="glyphicon glyphicon-calendar"--}}></span>
        </span>
        </div>
		@if ( $errors->has($name) )
			<span class="help-block">
				<strong>{{ $errors->first($name) }}</strong>
			</span>
		@endif
	</div>
</div>