@extends('layouts.app')
@section('title', __('lang_v1.configure_dashboard', ['name' => $dashboard->name]))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.configure_dashboard', ['name' => $dashboard->name])</h1>
</section>

<section class="content">

	{!! Form::open([
		'url' => action('DashboardConfiguratorController@update', 
		['id' => $dashboard->id]), 'method' => 'put'])!!}
		@csrf
		<input type="hidden" name="configuration" 
			id="configuration_input" 
			value="{{json_encode($dashboard->configuration)}}">
    
    	<!-- Loop through the widgets available -->
		<div class="col-md-3 bg-gray @if(empty($dashboard->configuration)) hide @endif)" id="row_options_div">
			@foreach($available_widgets as $key => $value)
				@include('dashboard_configurator.partials.widget', ['widget' => $key])
			@endforeach
		</div>

		<div class="col-md-8 col-md-offset-1">
			<!-- Div where element are dropped -->
			<div id="droppable_container">
				@foreach($dashboard->configuration as $row)
					@include('dashboard_configurator.partials.row', ['ratio' => $row])
				@endforeach
			</div>

			<div class="mt-10">
				@include('dashboard_configurator.partials.row_options')
				<div class="row">
					<button class="btn btn-default btn-block" type="button" data-toggle="collapse" data-target=".row_options" aria-expanded="false" aria-controls="collapseExample" 
						id="add_row">
						<i class="fas fa-plus"></i>@lang('lang_v1.add_row')
					</button>
				</div>
			</div>
		</div>

		<div class="col-md-12 mt-15">
			<button class="btn btn-primary pull-right">
				@lang('messages.save')
			</button>
		</div>
	{!! Form::close() !!}
	
</section>

@endsection

@section('javascript')

	<style type="text/css">
		.draggable{
			height: 30px;
		    text-align: center;
		    background-color: white;
		    margin-top: 7px;
		    margin-bottom: 5px;
		    border: 1px solid rgb(173, 173, 173);
		    cursor: pointer;
		    background-color: palegreen;
		}
		.cell{
			border: 1px solid rgb(173, 173, 173);
    		min-height: 50px;
    		text-align: center;
		}
		.ui-droppable-hover{
			border: 2px dotted red;
		}
		.row_option > div{
			border: 1px solid rgb(173, 173, 173);
    		min-height: 50px;
    		text-align: center;
		}
		.row_option:hover{
			opacity: 0.8;
		}
		.sortable-placeholder{
			border: solid 1px rgb(173, 173, 173);
			background-color: red;
			min-height: 50px;
		}
		.border-1px-173{
			border: solid 1px rgb(173, 173, 173);
		}
	</style>

	<script type="text/javascript">
		$(document).ready(function(){
			var droppableOptions = {
    			accept: ".draggable",
		      	cursor: "crosshair",
		      	cursorAt: { left: 0 },
		      	zIndex: 100,
	      		drop: function(event, ui){
	      			type = ui.draggable.data('type');
	      			$(this).find('div.add_a_widget').remove();
	      			$(this).append(ui.draggable.clone());
	      			update_configuration_input();
	      		}
			}
			$('div.droppable').droppable(droppableOptions);

			$( ".draggable" ).draggable({
				helper: "clone",
			});

			//Append row on clicking the row options.
			$('div.row_option').click(function(){
				ratio = $(this).data('ratio').toString();

				row_html = '<div class="row border-1px-173" data-ratio="' + ratio + '"><div style="width:97% !important; float:left; display:flex">';
				ratio.split('-').forEach(function(ratio){
					//Explode through the ratio and create html divs.
					row_html = row_html +'<div class="droppable cell col-md-' + ratio + '"><div class="add_a_widget">' + '{{__("lang_v1.add_widget_here")}}' + '</div></div>';
				});
				row_html = row_html + '</div><div style="width:3% !important; display:inline; float:right; text-align:center"><i class="fas fa-grip-horizontal handle cursor-pointer" title="' + '{{__("lang_v1.move_row")}}' +'"></i> <i class="fas fa-times remove_row text-danger cursor-pointer" title="'+ '{{__("lang_v1.delete")}}'+'"></i> </div></div>';

				$('div#row_options_div').removeClass('hide');
				$('button#add_row').trigger('click');
				$('div#droppable_container').append(row_html);
				$('div.droppable').droppable(droppableOptions);
			});

			//Make it sortable.
			$( "#droppable_container" ).sortable({
				items: ".row",
				handle: ".handle",
				placeholder: "sortable-placeholder row",
				update: function(event, ui){
					update_configuration_input();
				}
		    });

			//Remove the row
		    $( "#droppable_container" ).on("click", ".remove_row", function() {
		    	if(confirm(LANG.sure)){
		    		$(this).closest('div.row').remove();
		    		update_configuration_input();
		    	}
		    });
		});
		

		    function update_configuration_input(){
		    	//Iterate through the element to save them in input field
      			var config = [];
      			$('div#droppable_container').find('div.row').each(function(){
      				temp = {'ratio': $(this).data('ratio'), 
      						'widgets': []};

      				$(this).find('div.droppable').each(function(){
      					widgets = []
      					$(this).find('div.draggable').each(function(){
      						widgets.push($(this).data('type'));
      					});
      					
      						temp.widgets.push(widgets);
      					
      				});

      				config.push(temp)
      			});

      			$('input#configuration_input').val(JSON.stringify(config));
		    }
	</script>
@endsection