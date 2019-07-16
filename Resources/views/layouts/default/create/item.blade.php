{!! Form::bsOpen($row,'store') !!}
<div class="row">
@foreach($_panel->fields() as $field)
@php
	$input='bs'.studly_case($field->type);
@endphp
	<div class="col-sm-{{ isset($field->col_bs_size)?$field->col_bs_size:12 }}">
	{!! Form::$input($field->name,(isset($field->value)?$field->value:null)) !!}
	</div>
@endforeach
</div>
{{Form::bs3Submit('')}}
{!! Form::close() !!}