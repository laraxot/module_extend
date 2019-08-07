{!! Form::bsOpen($row,'store') !!}

@foreach($_panel->fields() as $field)
@php
	$input='bs'.studly_case($field->type);
@endphp
	{!! Form::$input($field->name) !!}
@endforeach
{{Form::bs3Submit('')}}
{!! Form::close() !!}