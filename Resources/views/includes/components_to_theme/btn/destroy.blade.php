@php
	Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.js');
	Theme::add('theme/bc/sweetalert2/dist/sweetalert2.min.css');
	Theme::add('extend::js/btnDeleteX2.js');
@endphp
<a class="{{ $btn_class }}" href="#" data-token="{{ csrf_token() }}" data-id="{{ $id }}" data-href="{{ $route }}" data-toggle="tooltip" title="Cancella">
	{{--      
	<i class="fa fas fa-trash-alt fa-trash-o fa-fw" aria-hidden="true"></i>
	--}}
	<i class="far fa-trash-alt"></i>
</a>