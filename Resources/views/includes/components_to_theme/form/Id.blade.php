@component($blade_component,compact('name','value','attributes','comp_view'))
	@slot('label')
	ID
	@endslot
	@slot('input')
	{{ $value }}
	@endslot
@endcomponent