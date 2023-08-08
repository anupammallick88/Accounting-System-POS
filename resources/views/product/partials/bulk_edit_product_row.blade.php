<tbody class="product_rows" id="product_{{$product->id}}">
	<tr class="bg-green">
		<td>{{$product->name}} ({{$product->sku}})</td>
		<td>
			{!! Form::select('products[' . $product->id . '][category_id]', $categories, $product->category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm category_id', 'style' => 'width: 100%;']); !!}
		</td>
		<td>
			{!! Form::select('products[' . $product->id . '][sub_category_id]', !empty($sub_categories[$product->category_id]) ? $sub_categories[$product->category_id] : [], $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm sub_category_id', 'style' => 'width: 100%;']); !!}
		</td>
		<td>
			{!! Form::select('products[' . $product->id . '][brand_id]', $brands, $product->brand_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm', 'style' => 'width: 100%;']); !!}
		</td>
		<td>
			{!! Form::select('products[' . $product->id . '][tax]', $taxes, $product->tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2 input-sm row_tax', 'style' => 'width: 100%;'],$tax_attributes); !!}
		</td>
		<td>
			{!! Form::select('products[' . $product->id . '][product_locations][]', $business_locations, $product->product_locations->pluck('id'), ['class' => 'form-control select2', 'multiple']); !!}
		</td>
	<tr>
	<tr>
		<td colspan="6">
			<table class="table">
				<thead>
					<tr>
						<th>@lang('lang_v1.variation')</th>
						<th>@lang('product.default_purchase_price')</th>
						<th>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</th>
                		<th>@lang('product.default_selling_price')</th>
                		<th>@lang('lang_v1.group_price')</th>
					</tr>
				</thead>
				<tbody>
				@foreach($product->variations as $variation)
					<tr class="variation_row">
						@include('product.partials.bulk_edit_variation_row')
					</tr>
				@endforeach
				</tbody>
			</table>
		</td>
	</tr>
</tbody>