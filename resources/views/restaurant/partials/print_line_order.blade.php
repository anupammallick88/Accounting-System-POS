<h4 class="text-center">#{{$order->invoice_no}}</h4>
<table class="table table-bordered table-striped">
	<tr>
		<th>
			@lang('restaurant.placed_at')
		</th>
		<td>
			{{@format_date($order->created_at)}} {{ @format_time($order->created_at)}}
		</td>
	</tr>
	<tr>
		<th>
			@lang('restaurant.order_status')
		</th>
		<td>
			@lang('restaurant.order_statuses.' . $order->res_line_order_status)
		</td>
	</tr>
	<tr>
		<th>
			@lang('contact.customer')
		</th>
		<td>
			{{$order->customer_name}}
		</td>
	</tr>
	<tr>
		<th>
			@lang('restaurant.table')
		</th>
		<td>
			{{$order->table_name}}
		</td>
	</tr>
    <tr>
    	<th>
    		@lang('restaurant.service_staff')
    	</th>
    	<td>
    		{{$order->service_staff_name ?? ''}}
    	</td>
    </tr>
	<tr>
		<th>
			@lang('sale.location')
		</th>
		<td>
			{{$order->business_location}}
		</td>
	</tr>
    <tr>
          <th>
                @lang('sale.product')
          </th>
          <td>
                {{$order->product_name}}
                @if($order->product_type == 'variable')
                       - {{$order->product_variation_name}} - {{$order->variation_name}} 
                @endif
                @if(!empty($order->modifiers) && count($order->modifiers) > 0)
                      @foreach($order->modifiers as $key => $modifier)
                            <br>{{$modifier->product->name ?? ''}}
                            @if(!empty($modifier->variations))
                                  - {{$modifier->variations->name ?? ''}}
                                  @if(!empty($modifier->variations->sub_sku))
                                        ({{$modifier->variations->sub_sku ?? ''}})
                                  @endif
                            @endif
                      @endforeach
                @endif
          </td>
    </tr>
    <tr>
    	<th>
    		@lang('lang_v1.quantity')
    	</th>
    	<td>
    		{{$order->quantity}}{{$order->unit}}
    	</td>
    </tr>
    <tr>
    	<th>
    		@lang('lang_v1.description')
    	</th>
    	<td> 
    		{!! nl2br($order->sell_line_note ?? '') !!}
    	</td>
    </tr>
</table>