<div class="row">
	<div class="col-md-12">
		<h4>{{$stock_details['variation']}}</h4>
	</div>
	<div class="col-md-4 col-xs-4">
		<strong>@lang('lang_v1.quantities_in')</strong>
		<table class="table table-condensed">
			<tr>
				<th>@lang('report.total_purchase')</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_purchase']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
			<tr>
				<th>@lang('lang_v1.opening_stock')</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_opening_stock']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
			<tr>
				<th>@lang('lang_v1.total_sell_return')</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_sell_return']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
			<tr>
				<th>@lang('lang_v1.stock_transfers') (@lang('lang_v1.in'))</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_purchase_transfer']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
		</table>
	</div>
	<div class="col-md-4 col-xs-4">
		<strong>@lang('lang_v1.quantities_out')</strong>
		<table class="table table-condensed">
			<tr>
				<th>@lang('lang_v1.total_sold')</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_sold']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
			<tr>
				<th>@lang('report.total_stock_adjustment')</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_adjusted']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
			<tr>
				<th>@lang('lang_v1.total_purchase_return')</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_purchase_return']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
			
			<tr>
				<th>@lang('lang_v1.stock_transfers') (@lang('lang_v1.out'))</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['total_sell_transfer']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
		</table>
	</div>

	<div class="col-md-4 col-xs-4">
		<strong>@lang('lang_v1.totals')</strong>
		<table class="table table-condensed">
			<tr>
				<th>@lang('report.current_stock')</th>
				<td>
					<span class="display_currency" data-is_quantity="true">{{$stock_details['current_stock']}}</span> {{$stock_details['unit']}}
				</td>
			</tr>
		</table>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<hr>
		<table class="table table-slim" id="stock_history_table">
			<thead>
			<tr>
				<th>@lang('lang_v1.type')</th>
				<th>@lang('lang_v1.quantity_change')</th>
				<th>@lang('lang_v1.new_quantity')</th>
				<th>@lang('lang_v1.date')</th>
				<th>@lang('purchase.ref_no')</th>
			</tr>
			</thead>
			<tbody>
			@forelse($stock_history as $history)
				<tr>
					<td>{{$history['type_label']}}</td>
					<td>@if($history['quantity_change'] > 0 ) +<span class="display_currency" data-is_quantity="true">{{$history['quantity_change']}}</span> @else <span class="display_currency" data-is_quantity="true">{{$history['quantity_change']}}</span> @endif</td>
					<td><span class="display_currency" data-is_quantity="true">{{$history['stock']}}</span></td>
					<td>{{@format_datetime($history['date'])}}</td>
					<td>
						{{$history['ref_no']}}

						@if(!empty($history['additional_notes']))
							@if(!empty($history['ref_no']))
							<br>
							@endif
							{{$history['additional_notes']}}
						
						@endif
					</td>
				</tr>
			@empty
				<tr><td colspan="5" class="text-center">
					@lang('lang_v1.no_stock_history_found')
				</td></tr>
			@endforelse
			</tbody>
		</table>
	</div>
</div>