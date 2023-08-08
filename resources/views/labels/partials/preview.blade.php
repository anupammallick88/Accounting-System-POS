<title>{{__('barcode.print_labels')}}</title>
<button class="btn btn-success" onclick="window.print()">Print</button>
<div id="preview_body">
@php
	$loop_count = 0;
@endphp
@foreach($product_details as $details)
	@while($details['qty'] > 0)
		@php
			$loop_count += 1;
			$is_new_row = ($barcode_details->stickers_in_one_row == 1 || ($loop_count % $barcode_details->stickers_in_one_row) == 1) ? true : false;
 
			$is_new_paper = ($barcode_details->is_continuous && $is_new_row) || (!$barcode_details->is_continuous && ($loop_count % $barcode_details->stickers_in_one_sheet == 1));

			$is_paper_end = (($barcode_details->is_continuous && ($loop_count % $barcode_details->stickers_in_one_row == 0)) || (!$barcode_details->is_continuous && ($loop_count % $barcode_details->stickers_in_one_sheet == 0)));

			
		@endphp

		@if($is_new_paper)
			{{-- Actual Paper --}}
			<div style="@if(!$barcode_details->is_continuous) height:{{$barcode_details->paper_height}}in !important; @else height:{{$barcode_details->height}}in !important; @endif width:{{$barcode_details->paper_width}}in !important; line-height: 16px !important;" class="@if(!$barcode_details->is_continuous) label-border-outer @endif">

			{{-- Paper Internal --}}
			<div style="@if(!$barcode_details->is_continuous)margin-top:{{$barcode_details->top_margin}}in !important; margin-bottom:{{$barcode_details->top_margin}}in !important; margin-left:{{$barcode_details->left_margin}}in !important;margin-right:{{$barcode_details->left_margin}}in !important;@endif" class="label-border-internal">
		@endif

		@if((!$barcode_details->is_continuous) && ($loop_count % $barcode_details->stickers_in_one_sheet) <= $barcode_details->stickers_in_one_row)
			@php $first_row = true @endphp
		@elseif($barcode_details->is_continuous && ($loop_count <= $barcode_details->stickers_in_one_row) )
			@php $first_row = true @endphp
		@else
			@php $first_row = false @endphp
		@endif

		<div style="height:{{$barcode_details->height}}in !important; line-height: {{$barcode_details->height}}in; width:{{$barcode_details->width}}in !important; display: inline-block; @if(!$is_new_row) margin-left:{{$barcode_details->col_distance}}in !important; @endif @if(!$first_row)margin-top:{{$barcode_details->row_distance}}in !important; @endif" class="sticker-border text-center">
		<div style="display:inline-block;vertical-align:middle;line-height:16px !important;">
			{{-- Business Name --}}
			@if(!empty($print['business_name']))
				<b style="display: block !important" class="text-uppercase">{{$business_name}}</b>
			@endif

			{{-- Product Name --}}
			@if(!empty($print['name']))
				<span style="display: block !important">
					{{$details['details']->product_actual_name}}
				</span>
			@endif

			{{-- Variation --}}
			@if(!empty($print['variations']) && $details['details']->is_dummy != 1)
				<span style="display: block !important">
					<b>{{$details['details']->product_variation_name}}</b>:{{$details['details']->variation_name}}
				</span>
				
			@endif

			{{-- Price --}}
			@if(!empty($print['price']))
				<b>@lang('lang_v1.price'):</b>
				{{session('currency')['symbol'] ?? ''}}

				@if($print['price_type'] == 'inclusive')
					{{@num_format($details['details']->sell_price_inc_tax)}}
				@else
					{{@num_format($details['details']->default_sell_price)}}
				@endif
			@endif

			<br>
			{{-- Barcode --}}
			<img class="center-block" style="max-width:90%; !important;height: {{$barcode_details->height*0.24}}in !important;" src="data:image/png;base64,{{DNS1D::getBarcodePNG($details['details']->sub_sku, $details['details']->barcode_type, 3,30,array(39, 48, 54), true)}}">

		</div>
		</div>

		@if($is_paper_end)
			{{-- Actual Paper --}}
			</div>

			{{-- Paper Internal --}}
			</div>
		@endif

		@php
			$details['qty'] = $details['qty'] - 1;
		@endphp
	@endwhile
@endforeach

@if(!$is_paper_end)
	{{-- Actual Paper --}}
	</div>

	{{-- Paper Internal --}}
	</div>
@endif


</div>

<script type="text/javascript">

</script>

<style type="text/css">

	.text-center{
		text-align: center;
	}

	.text-uppercase{
		text-transform: uppercase;
	}

	/*Css related to printing of barcode*/
	.label-border-outer{
	    border: 0.1px solid grey !important;
	}
	.label-border-internal{
	    /*border: 0.1px dotted grey !important;*/
	}
	.sticker-border{
	    border: 0.1px dotted grey !important;
	    overflow: hidden;
	    box-sizing: border-box;
	}
	#preview_box{
	    padding-left: 30px !important;
	}
	@media print{
	    .content-wrapper{
	      border-left: none !important; /*fix border issue on invoice*/
	    }
	    .label-border-outer{
	        border: none !important;
	    }
	    .label-border-internal{
	        border: none !important;
	    }
	    .sticker-border{
	        border: none !important;
	    }
	    #preview_box{
	        padding-left: 0px !important;
	    }
	    #toast-container{
	        display: none !important;
	    }
	    .tooltip{
	        display: none !important;
	    }
	    .btn{
	    	display: none !important;
	    }
	}

	@media print{
		#preview_body{
			display: block !important;
		}
	}
	@page {
		size: {{$barcode_details->paper_width}}in @if(!$barcode_details->is_continuous && $barcode_details->paper_height != 0){{$barcode_details->paper_height}}in @endif;

		/*width: {{$barcode_details->paper_width}}in !important;*/
		/*height:@if($barcode_details->paper_height != 0){{$barcode_details->paper_height}}in !important @else auto @endif;*/
		margin-top: 0in;
		margin-bottom: 0in;
		margin-left: 0in;
		margin-right: 0in;
		
		@if($barcode_details->is_continuous)
			/*page-break-inside : avoid !important;*/
		@endif
	}
</style>