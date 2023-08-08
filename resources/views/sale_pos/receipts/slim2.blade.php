<!-- business information here -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- <link rel="stylesheet" href="style.css"> -->
        <title>Receipt-{{$receipt_details->invoice_no}}</title>
    </head>
    <body>
        <div class="ticket">
        	@if(!empty($receipt_details->logo))
        		<div class="text-box centered">
        			<img style="max-height: 100px; width: auto;" src="{{$receipt_details->logo}}" alt="Logo">
        		</div>
        	@endif
        	<div class="text-box">
            <p class="centered">
            	<!-- Header text -->
            	@if(!empty($receipt_details->header_text))
            		<span class="headings">{!! $receipt_details->header_text !!}</span>
					<br/>
				@endif

				<!-- business information here -->
				@if(!empty($receipt_details->display_name))
					<span class="headings">
						{{$receipt_details->display_name}}
					</span>
					<br/>
				@endif
				
				@if(!empty($receipt_details->address))
					{!! $receipt_details->address !!}
					<br/>
				@endif

				@if(!empty($receipt_details->contact))
					<br/>{!! $receipt_details->contact !!}
				@endif
				@if(!empty($receipt_details->contact) && !empty($receipt_details->website))
					<br/> 
				@endif
				@if(!empty($receipt_details->website))
					{{ $receipt_details->website }}
				@endif
				@if(!empty($receipt_details->location_custom_fields))
					<br>{{ $receipt_details->location_custom_fields }}
				@endif

				@if(!empty($receipt_details->sub_heading_line1))
					{{ $receipt_details->sub_heading_line1 }}<br/>
				@endif
				@if(!empty($receipt_details->sub_heading_line2))
					{{ $receipt_details->sub_heading_line2 }}<br/>
				@endif
				@if(!empty($receipt_details->sub_heading_line3))
					{{ $receipt_details->sub_heading_line3 }}<br/>
				@endif
				@if(!empty($receipt_details->sub_heading_line4))
					{{ $receipt_details->sub_heading_line4 }}<br/>
				@endif		
				@if(!empty($receipt_details->sub_heading_line5))
					{{ $receipt_details->sub_heading_line5 }}<br/>
				@endif

				@if(!empty($receipt_details->tax_info1))
					<br><b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
				@endif

				@if(!empty($receipt_details->tax_info2))
					<b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
				@endif			
			</p>
			</div>
			<div class="border-top textbox-info">
				<p class="f-left"><strong>{!! $receipt_details->invoice_no_prefix !!}</strong></p>
				<p class="f-right ex1">
					{{$receipt_details->invoice_no}}
				</p>
			</div>
			<div class="textbox-info">
				<p class="f-left"><strong>{!! $receipt_details->date_label !!}</strong></p>
				<p class="f-right ex1">
					{{$receipt_details->invoice_date}}
				</p>
			</div>
			
			@if(!empty($receipt_details->due_date_label))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->due_date_label}}</strong></p>
					<p class="f-right">{{$receipt_details->due_date ?? ''}}</p>
				</div>
			@endif

			@if(!empty($receipt_details->sales_person_label))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->sales_person_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->sales_person}}</p>
				</div>
			@endif
			@if(!empty($receipt_details->commission_agent_label))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->commission_agent_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->commission_agent}}</p>
				</div>
			@endif

			@if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->brand_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->repair_brand}}</p>
				</div>
			@endif

			@if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->device_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->repair_device}}</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->model_no_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->repair_model_no}}</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->serial_no_label}}</strong></p>
				
					<p class="f-right ex1">{{$receipt_details->repair_serial_no}}</p>
				</div>
			@endif

			@if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!! $receipt_details->repair_status_label !!}
					</strong></p>
					<p class="f-right ex1">
						{{$receipt_details->repair_status}}
					</p>
				</div>
        	@endif

        	@if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
	        	<div class="textbox-info">
	        		<p class="f-left"><strong>
	        			{!! $receipt_details->repair_warranty_label !!}
	        		</strong></p>
	        		<p class="f-right ex1">
	        			{{$receipt_details->repair_warranty}}
	        		</p>
	        	</div>
        	@endif

        	<!-- Waiter info -->
			@if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
	        	<div class="textbox-info">
	        		<p class="f-left"><strong>
	        			{!! $receipt_details->service_staff_label !!}
	        		</strong></p>
	        		<p class="f-right ex1">
	        			{{$receipt_details->service_staff}}
					</p>
	        	</div>
	        @endif

	        @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
	        	<div class="textbox-info">
	        		<p class="f-left"><strong>
	        			@if(!empty($receipt_details->table_label))
							<b>{!! $receipt_details->table_label !!}</b>
						@endif
	        		</strong></p>
	        		<p class="f-right ex1">
	        			{{$receipt_details->table}}
	        		</p>
	        	</div>
	        @endif

	        <!-- customer info -->
	        <div class="textbox-info">
	        	<p style="vertical-align: top;"><strong>
	        		{{$receipt_details->customer_label ?? ''}}
	        	</strong></p>

	        	<p>
	        		@if(!empty($receipt_details->customer_info))
	        			<div class="bw">
						{!! $receipt_details->customer_info !!}
						</div>
					@endif
	        	</p>
	        </div>
			
			@if(!empty($receipt_details->client_id_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{{ $receipt_details->client_id_label }}
					</strong></p>
					<p class="f-right ex1">
						{{ $receipt_details->client_id }}
					</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->customer_tax_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{{ $receipt_details->customer_tax_label }}
					</strong></p>
					<p class="f-right ex1">
						{{ $receipt_details->customer_tax_number }}
					</p>
				</div>
			@endif

			@if(!empty($receipt_details->customer_custom_fields))
				<div class="textbox-info">
					<p class="centered">
						{!! $receipt_details->customer_custom_fields !!}
					</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->customer_rp_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{{ $receipt_details->customer_rp_label }}
					</strong></p>
					<p class="f-right ex1">
						{{ $receipt_details->customer_total_rp }}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_1_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_1_label!!} 
					</strong></p>
					<p class="f-right ex1">
						{!!$receipt_details->shipping_custom_field_1_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_2_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_2_label!!} 
					</strong></p>
					<p class="f-right ex1">
						{!!$receipt_details->shipping_custom_field_2_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_3_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_3_label!!} 
					</strong></p>
					<p class="f-right ex1">
						{!!$receipt_details->shipping_custom_field_3_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_4_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_4_label!!} 
					</strong></p>
					<p class="f-right ex1">
						{!!$receipt_details->shipping_custom_field_4_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_5_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_5_label!!} 
					</strong></p>
					<p class="f-right ex1">
						{!!$receipt_details->shipping_custom_field_5_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->sale_orders_invoice_no))
				<div class="textbox-info">
					<p class="f-left"><strong>
						@lang('restaurant.order_no')
					</strong></p>
					<p class="f-right ex1">
						{!!$receipt_details->sale_orders_invoice_no ?? ''!!}
					</p>
				</div>
			@endif

			@if(!empty($receipt_details->sale_orders_invoice_date))
				<div class="textbox-info">
					<p class="f-left"><strong>
						@lang('lang_v1.order_dates')
					</strong></p>
					<p class="f-right ex1">
						{!!$receipt_details->sale_orders_invoice_date ?? ''!!}
					</p>
				</div>
			@endif
			<div class="bb-lg mt-15 mb-10"></div>
            <table style="padding-top: 5px !important" class="border-bottom width-100 table-f-12 mb-10">
                 <thead>
                    <th>Description</th>
                    <th>Qty</th>
                    <!--<th>Unit price</th>-->
                    <th>Total</th>
                </thead>
                <tbody>
                	@forelse($receipt_details->lines as $line)
	                    <tr class="bb-lg">
	                        <td class="description">
	                        	<div style="display:flex; width: 100%;">
	                        	<!--	<p class="m-0 mt-5" style="white-space: nowrap;">#{{$loop->iteration}}.&nbsp;</p>-->
	                        		<p class="text-left m-0 mt-5 pull-left">{{$line['name']}}  
			                        	@if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])) <br> {{$line['brand']}} @endif @if(!empty($line['cat_code']))<br> {{$line['cat_code']}}@endif
			                        	@if(!empty($line['product_custom_fields']))<br> {{$line['product_custom_fields']}} @endif
			                        	@if(!empty($line['sell_line_note']))
			                        	<br>
	                        			<span class="f-8">
			                        	{{$line['sell_line_note']}}
			                        	</span>
			                        	@endif
			                        	@if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif
			                        	@if(!empty($line['product_expiry']))<br> {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif

			                        	@if(!empty($line['variation']))
			                        		<br>
			                        		{{$line['product_variation']}} {{$line['variation']}}
			                        	@endif
			                        	@if(!empty($line['warranty_name']))
			                            	<br>
			                            	<small>
			                            		{{$line['warranty_name']}}
			                            	</small>
			                            @endif
			                            @if(!empty($line['warranty_exp_date']))
			                            	<br><small>
			                            		- {{@format_date($line['warranty_exp_date'])}}
			                            	</small>
			                            @endif
			                            @if(!empty($line['warranty_description']))<br>
			                            	<small> {{$line['warranty_description'] ?? ''}}</small>
			                            @endif
	                        		</p>
	                        	</div></td>
	                        	<td>
	                        	<div style="display:flex; width: 100%;">
	                        		<p class="text-center" style="direction: inherit;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	                        		{{(int)($line['quantity'])}} <br>{{($line['units'])}} 
	                        			@if(empty($receipt_details->hide_price))
	                        			
	                        		</td>
	                        			<!--x <td>{{$line['unit_price_inc_tax']}} </td>
	                        			
	                        			@if(!empty($line['line_discount']) && $line['line_discount'] != 0)
	                        				- {{$line['line_discount']}}
	                        			@endif-->
	                        			@endif
	                        		</p>
	                        		@if(empty($receipt_details->hide_price))
	                        	
	                        	<td>
	                        		<p class="text-right ex1 ">{{$line['line_total']}}</p>
	                        		@endif
	                        	</div>
	                        </td>
	                    </tr>
	                    @if(!empty($line['modifiers']))
							@foreach($line['modifiers'] as $modifier)
								<tr>
									<td>
										<div style="display:flex;">
	                        				<p style="width: 28px;" class="m-0">
	                        				</p>
	                        				<p class="text-left width-60 m-0" style="margin:0;">
	                        					{{$modifier['name']}} 
	                        					@if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
			                            		@if(!empty($modifier['sell_line_note']))({{$modifier['sell_line_note']}}) @endif
	                        				</p>
	                        				<p class="text-right width-40 m-0">
	                        					{{$modifier['variation']}}
	                        				</p>
	                        			</div>	
	                        			<div style="display:flex;">
	                        				<p style="width: 28px;"></p>
	                        				<p class="text-left width-50 quantity">
	                        					{{$modifier['quantity']}}
	                        					@if(empty($receipt_details->hide_price))
	                        					x {{$modifier['unit_price_inc_tax']}}
	                        					@endif
	                        				</p>
	                        				<p class="text-right width-50 price">
	                        					{{$modifier['line_total']}}
	                        				</p>
	                        			</div>		                             
			                        </td>
			                    </tr>
							@endforeach
						@endif
                    @endforeach
                </tbody>
            </table>
            @if(!empty($receipt_details->total_quantity_label))
				<div class="flex-box">
					<p class="left text-left">
						{!! $receipt_details->total_quantity_label !!}
					</p>
					<p class="width-50 text-right ex1">
						{{$receipt_details->total_quantity}}
					</p>
				</div>
			@endif
			@if(empty($receipt_details->hide_price))
            <div class="flex-box">
                <p class="left text-left">
                	<strong>{!! $receipt_details->subtotal_label !!}</strong>
                </p>
                <p class="width-50 text-right ex1">
                	<strong>{{$receipt_details->subtotal}}</strong>
                </p>
            </div>

            <!-- Shipping Charges -->
			@if(!empty($receipt_details->shipping_charges))
				<div class="flex-box">
					<p class="left text-left">
						{!! $receipt_details->shipping_charges_label !!}
					</p>
					<p class="width-50 text-right ex1">
						{{$receipt_details->shipping_charges}}
					</p>
				</div>
			@endif

			@if(!empty($receipt_details->packing_charge))
				<div class="flex-box">
					<p class="left text-left">
						{!! $receipt_details->packing_charge_label !!}
					</p>
					<p class="width-50 text-right ex1">
						{{$receipt_details->packing_charge}}
					</p>
				</div>
			@endif

			<!-- Discount -->
			@if( !empty($receipt_details->discount) )
				<div class="flex-box">
					<p class="width-50 text-left">
						{!! $receipt_details->discount_label !!}
					</p>

					<p class="width-50 text-right ex1">
						(-) {{$receipt_details->discount}}
					</p>
				</div>
			@endif
			
			@if( !empty($receipt_details->total_line_discount) )
				<div class="flex-box">
					<p class="width-50 text-right ex1">
						{!! $receipt_details->line_discount_label !!}
					</p>

					<p class="width-50 text-right ex1">
						(-) {{$receipt_details->total_line_discount}}
					</p>
				</div>
			@endif

			@if(!empty($receipt_details->reward_point_label) )
				<div class="flex-box">
					<p class="width-50 text-left">
						{!! $receipt_details->reward_point_label !!}
					</p>

					<p class="width-50 text-right ex1">
						(-) {{$receipt_details->reward_point_amount}}
					</p>
				</div>
			@endif

			@if( !empty($receipt_details->tax) )
				<div class="flex-box">
					<p class="width-50 text-left">
						{!! $receipt_details->tax_label !!}
					</p>
					<p class="width-50 text-right ex1">
						(+) {{$receipt_details->tax}}
					</p>
				</div>
			@endif

			@if( $receipt_details->round_off_amount > 0)
				<div class="flex-box">
					<p class="width-50 text-left">
						{!! $receipt_details->round_off_label !!} 
					</p>
					<p class="width-50 text-right ex1">
						{{$receipt_details->round_off}}
					</p>
				</div>
			@endif

			<div class="flex-box">
				<p class="width-50 text-left">
					<strong>{!! $receipt_details->total_label !!}</strong>
				</p>
				<p class="width-50 text-right ex1">
					<strong>{{$receipt_details->total}}</strong>
				</p>
			</div>
			@if(!empty($receipt_details->total_in_words))
				<p colspan="2" class="text-right mb-0 ex1">
					<small>
					({{$receipt_details->total_in_words}})
					</small>
				</p>
			@endif
			@if(!empty($receipt_details->payments))
				@foreach($receipt_details->payments as $payment)
					<div class="flex-box">
						<p class="width-50 text-left">{{$payment['method']}} </p>
						<p class="width-50 text-right ex1">{{$payment['amount']}}</p>
					</div>
				@endforeach
			@endif
            <!-- Total Paid-->
				@if(!empty($receipt_details->total_paid))
					<div class="flex-box">
						<p class="width-50 text-left">
							{!! $receipt_details->total_paid_label !!}
						</p>
						<p class="width-50 text-right ex1">
							{{$receipt_details->total_paid}}
						</p>
					</div>
				@endif

				<!-- Total Due-->
				@if(!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
					<div class="flex-box">
						<p class="width-50 text-left">
							{!! $receipt_details->total_due_label !!}
						</p>
						<p class="width-50 text-right ex1">
							{{$receipt_details->total_due}}
						</p>
					</div>
				@endif

				@if(!empty($receipt_details->all_due))
					<div class="flex-box">
						<p class="width-50 text-left">
							{!! $receipt_details->all_bal_label !!}
						</p>
						<p class="width-50 text-right ex1">
							{{$receipt_details->all_due}}
						</p>
					</div>
				@endif
			@endif
            <div class="border-bottom width-100">&nbsp;</div>
            @if(empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label) )
	            <!-- tax -->
	            @if(!empty($receipt_details->taxes))
	            	<table class="border-bottom width-100 table-f-12">
	            		<tr>
	            			<th colspan="2" class="text-center">{{$receipt_details->tax_summary_label}}</th>
	            		</tr>
	            		@foreach($receipt_details->taxes as $key => $val)
	            			<tr>
	            				<td class="left">{{$key}}</td>
	            				<td class="right ex1">{{$val}}</td>
	            			</tr>
	            		@endforeach
	            	</table>
	            @endif
            @endif

            @if(!empty($receipt_details->additional_notes))
	            <p class="centered" >
	            	{!! nl2br($receipt_details->additional_notes) !!}
	            </p>
            @endif

            {{-- Barcode --}}
			@if($receipt_details->show_barcode)
				<br/>
				<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
			@endif

			@if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_details))
				@php
					$qr_code_text = implode(', ', $receipt_details->qr_code_details);
				@endphp
				<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
			@endif

			@if(!empty($receipt_details->footer_text))
				<p class="centered">
					{!! $receipt_details->footer_text !!}
				</p>
			@endif
        </div>
        <button id="btnPrint" class="hidden-print">Print</button>
        <script src="script.js"></script> 
    </body>
</html>

<style type="text/css">
.f-8 {
	font-size: 8px !important;
}
@media print {
	* {
    	font-size: 12px;
    	font-family: 'Times New Roman';
    	word-break: break-all;
	}
	.f-8 {
		font-size: 8px !important;
	}

.headings{
	font-size: 12px;
	font-weight: 400;
	text-transform: uppercase;
}

.sub-headings{
	font-size: 15px;
	font-weight: 700;
}

.border-top{
    border-top: 1px solid #242424;
}
.border-bottom{
	border-bottom: 2px solid #242424;
}

.border-bottom-dotted{
	border-bottom: 2px solid darkgray;
}
p.ex1{
    margin-right: 5px;
}
td.serial_number, th.serial_number{
	width: 5%;
    max-width: 5%;
}

table {
    border-top: 1px solid black;
    border-collapse: collapse;
}
td, th {
  border: 1px solid #dddddd;
  text-align: center;
  padding: 0px;
}

td.description,
th.description {
    width: 75px;
    max-width: 75px;
}

td.quantity,
th.quantity {
    width: 15px;
    max-width: 15px;
    word-break: break-all;
}
td.unit_price, th.unit_price{
	width: 25px;
    max-width: 25px;
    word-break: break-all;
}

td.price,
th.price {
    width: 20px;
    max-width: 20px;
    word-break: break-all;
}

.centered {
    text-align: center;
    align-content: center;
}

.ticket {
    width: 100%;
    max-width: 100%;
}

img {
    max-width: inherit;
    width: auto;
}

    .hidden-print,
    .hidden-print * {
        display: none !important;
    }
}
.table-info {
	width: 100%;
}
.table-info tr:first-child td, .table-info tr:first-child th {
	padding-top: 8px;
}
.table-info th {
	text-align: left;
}
.table-info td {
	text-align: right;
}
.logo {
	float: left;
	width:35%;
	padding: 10px;
}

.text-with-image {
	float: left;
	width:65%;
}
.text-box {
	width: 100%;
	height: auto;
}
.m-0 {
	margin:0;
}
.textbox-info {
	clear: both;
}
.textbox-info p {
	margin-bottom: 0px
}
.flex-box {
	display: flex;
	width: 100%;
}
.flex-box p {
	width: 50%;
	margin-bottom: 0px;
	white-space: nowrap;
}

.table-f-12 th, .table-f-12 td {
	font-size: 12px;
	word-break: break-word;
}

.bw {
	word-break: break-word;
}
.bb-lg {
	border-bottom: 1px solid lightgray;
}
</style>