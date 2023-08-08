<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		    <h4 class="modal-title" id="modalTitle"> @lang('lang_v1.stock_adjustment_details') (<b>@lang('purchase.ref_no'):</b> #{{ $stock_adjustment->ref_no }})
		    </h4>
		</div>
		<div class="modal-body">
		  	<div class="row">
			    <div class="col-sm-12">
			      <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($stock_adjustment->transaction_date) }}</p>
			    </div>
			</div>
			<div class="row invoice-info">
				<div class="col-sm-4 invoice-col">
    				@lang('business.business'):
			     	 <address>
			        <strong>{{ $stock_adjustment->business->name }}</strong>
			        {{ $stock_adjustment->location->name }}
			        @if(!empty($stock_adjustment->location->landmark))
			          <br>{{$stock_adjustment->location->landmark}}
			        @endif
			        @if(!empty($stock_adjustment->location->city) || !empty($stock_adjustment->location->state) || !empty($stock_adjustment->location->country))
			          <br>{{implode(',', array_filter([$stock_adjustment->location->city, $stock_adjustment->location->state, $stock_adjustment->location->country]))}}
			        @endif
			        @if(!empty($stock_adjustment->location->mobile))
			          <br>@lang('contact.mobile'): {{$stock_adjustment->location->mobile}}
			        @endif
			        @if(!empty($stock_adjustment->location->email))
			          <br>@lang('business.email'): {{$stock_adjustment->location->email}}
			        @endif
			      </address>
			    </div>

			    <div class="col-sm-4 invoice-col">
			      	<b>@lang('purchase.ref_no'):</b> #{{ $stock_adjustment->ref_no }}<br/>
			      	<b>@lang('messages.date'):</b> {{ @format_date($stock_adjustment->transaction_date) }}<br/>
			      	<b>@lang('stock_adjustment.adjustment_type'):</b> {{ __('stock_adjustment.' . $stock_adjustment->adjustment_type) }}<br>
			      	<b>@lang('stock_adjustment.reason_for_stock_adjustment'):</b> {{ $stock_adjustment->additional_notes }}<br>
			    </div>
    		</div>

    		<div class="row">
    			<div class="col-sm-12 col-xs-12">
      				<div class="table-responsive">
      					<table class="table table-condensed bg-gray">
							<tr class="bg-green">
								<th>@lang('sale.product')</th>
								@if(!empty($lot_n_exp_enabled))
			                		<th>{{ __('lang_v1.lot_n_expiry') }}</th>
			              		@endif
								<th>@lang('sale.qty')</th>
								<th>@lang('sale.unit_price')</th>
								<th>@lang('sale.subtotal')</th>
							</tr>
							@foreach( $stock_adjustment->stock_adjustment_lines as $stock_adjustment_line )
								<tr>
									<td>
										{{ $stock_adjustment_line->variation->full_name }}
									</td>
									@if(!empty($lot_n_exp_enabled))
						                <td>{{ $stock_adjustment_line->lot_details->lot_number ?? '--' }}
						                    @if( session()->get('business.enable_product_expiry') == 1 && !empty($stock_adjustment_line->lot_details->exp_date))
						                    ({{@format_date($stock_adjustment_line->lot_details->exp_date)}})
						                    @endif
						                </td>
						            @endif
									<td>
										{{@format_quantity($stock_adjustment_line->quantity)}}
									</td>
									<td>
										{{@num_format($stock_adjustment_line->unit_price)}}
									</td>
									<td>
										{{@num_format($stock_adjustment_line->unit_price * $stock_adjustment_line->quantity)}}
									</td>
								</tr>
							@endforeach
						</table>
      				</div>
     			</div>
     			<div class="col-md-6 col-md-offset-6 col-sm-12 col-xs-12">
				    <div class="table-responsive">
				        <table class="table no-border">
				          	<tr>
				            	<th>@lang('stock_adjustment.total_amount'): </th>
				            	<td><span class="display_currency pull-right" data-currency_symbol="true">{{ $stock_adjustment->final_total }}</span></td>
				          	</tr>
				          	<tr>
				            	<th>@lang('stock_adjustment.total_amount_recovered'): </th>
				            	<td><span class="display_currency pull-right" data-currency_symbol="true">{{ $stock_adjustment->total_amount_recovered }}</span></td>
				          	</tr>
				      	</table>
				  	</div>
				</div>
    		</div>
    		<div class="row">
		      <div class="col-md-12">
		            <strong>{{ __('lang_v1.activities') }}:</strong><br>
		            @includeIf('activity_log.activities')
		        </div>
		    </div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary no-print" aria-label="Print" 
			onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
			</button>
			<button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
		</div>
	</div>
</div>