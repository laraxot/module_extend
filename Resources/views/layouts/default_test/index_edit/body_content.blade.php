@php

//{!! Form::bsBtnCreate(['txt'=>__($item_view.'.btn.new')]) !!}
//ddd($second_last);
//$user=$item0;
@endphp
{{-- --}}
@foreach($rows as $key=>$row)
	@include($item_view,['key'=>$key,'row'=>$row])
@endforeach
{{ $rows->links() }}
{{-- 
{!! Form::bsOpen($second_last,'index_edit','index_edit') !!}
$types=camel_case(str_plural($container1));
{{ Form::bsMultiCheckbox($types) }}
{{ Form::bsMultiRating($types) }}
{{Form::submit('Salva ed esci',['class'=>'submit btn btn-success green-meadow'])}}
--}}

{!! Form::close() !!}
