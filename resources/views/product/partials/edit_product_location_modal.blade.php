<div class="modal fade" id="edit_product_location_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
	    <div class="modal-content">
	    	{!! Form::open(['url' => action('ProductController@updateProductLocation'), 'method' => 'post', 'id' => 'edit_product_location_form' ]) !!}
		    	<div class="modal-header">
			    	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				      <h4 class="modal-title"><span class="add_to_location_title hide">@lang( 'lang_v1.add_location_to_the_selected_products' )</span><span class="remove_from_location_title hide">@lang( 'lang_v1.remove_location_from_the_selected_products' )</span></h4>
			    </div>
			    <div class="modal-body">
			    	<div class="form-group">
		                {!! Form::label('product_location',  __('purchase.business_location') . ':') !!}
		                {!! Form::select('product_location[]', $business_locations, null, ['class' => 'form-control', 'style' => 'width:100%', 'required', 'multiple', 'id' => 'product_location']); !!}
		                {!! Form::hidden('products', null, ['id' => 'products_to_update_location']) !!}

		                {!! Form::hidden('update_type', null, ['id' => 'update_type']) !!}
		            </div>
			    </div>
			    <div class="modal-footer">
		      		<button type="submit" class="btn btn-primary" id="update_product_location">@lang( 'messages.save' )</button>
		      		<button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
		    	</div>
	    	{!! Form::close() !!}
	    </div>
    </div>
</div>