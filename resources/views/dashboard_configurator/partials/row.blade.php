@php
	$ratios = explode('-', $row['ratio']);
@endphp

<div class="row border-1px-173" data-ratio="{{$row['ratio']}}">
	<div style="width:97% !important; float:left; display:flex">
		@foreach($ratios as $key => $ratio)
			<div class="droppable cell col-md-{{$ratio}}">
				@if(empty($row['widgets'][$key]))
					<div class="add_a_widget">
						{{__("lang_v1.add_widget_here")}}
					</div>
				@else
					@foreach($row['widgets'][$key] as $widget)
						@include('dashboard_configurator.partials.widget', ['widget' => $widget])
					@endforeach
				@endif
			</div>
		@endforeach
	</div>
	<div style="width:3% !important; display:inline; float:right; text-align:center">
		<i class="fas fa-grip-horizontal handle cursor-pointer" 
			title='{{__("lang_v1.move_row")}}'></i>
		<i class="fas fa-times remove_row text-danger cursor-pointer" title='{{__("messages.delete")}}'></i>
	</div>
</div>