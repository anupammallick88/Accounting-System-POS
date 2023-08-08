@if(isset($available_widgets[$widget]))
	<div class="col-md-12 draggable" data-type="{{$widget}}">
		{{$available_widgets[$widget]['title']}}
	</div>
@endif