<td>
	@if($product->type == 'variable')
		{{ $variation->product_variation->name}}
		- {{ $variation->name}} ({{ $variation->sub_sku}})
	@endif
</td>
<td>
<div class="input-group">
	<span class="input-group-addon"><small>@lang('product.exc_of_tax')</small></span>
	{!! Form::text('products[' . $product->id . '][variations][' . $variation->id . '][default_purchase_price]', @num_format($variation->default_purchase_price), ['placeholder' => __('product.exc_of_tax'), 'class' => 'form-control input-sm input_number pp_exc_tax']); !!}
</div>
<div class="input-group">
	<span class="input-group-addon"><small>@lang('product.inc_of_tax')</small></span>
	{!! Form::text('products[' . $product->id . '][variations][' . $variation->id . '][dpp_inc_tax]', @num_format($variation->dpp_inc_tax), ['placeholder' => __('product.inc_of_tax'), 'class' => 'form-control input-sm input_number pp_inc_tax']); !!}</td>
</div>
<td>
	{!! Form::text('products[' . $product->id . '][variations][' . $variation->id . '][profit_percent]', @num_format($variation->profit_percent), ['class' => 'form-control input-sm input_number profit_percent']); !!}
</td>
<td>
	<div class="input-group">
		<span class="input-group-addon"><small>@lang('product.exc_of_tax')</small></span>
		{!! Form::text('products[' . $product->id . '][variations][' . $variation->id . '][default_sell_price]', @num_format($variation->default_sell_price), ['placeholder' => __('product.exc_of_tax'), 'class' => 'form-control input-sm input_number sp_exc_tax']); !!}
	</div>

	<div class="input-group">
		<span class="input-group-addon"><small>@lang('product.inc_of_tax')</small></span>
		{!! Form::text('products[' . $product->id . '][variations][' . $variation->id . '][sell_price_inc_tax]', @num_format($variation->sell_price_inc_tax), ['placeholder' => __('product.dpp_inc_tax'), 'class' => 'form-control input-sm input_number sp_inc_tax']); !!}
	</div>
</td>
<td style="text-align: left;">
	@foreach($price_groups as $k => $v)
		@php
			$price_grp = $variation->group_prices->filter(function($item) use($k) {
			    return $item->price_group_id == $k;
			})->first();
		@endphp
		<div class="input-group" style="width: 100%;">
			<span class="input-group-addon"><small>{{$v}} -</small></span>
			{!! Form::text('products[' . $product->id . '][variations][' . $variation->id . '][group_prices][' . $k . ']', !empty($price_grp) ? @num_format($price_grp->price_inc_tax) : 0, ['class' => 'form-control input-sm input_number']); !!}
		</div>
	@endforeach
</td>