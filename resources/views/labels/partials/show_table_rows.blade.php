@forelse ($products as $product)
    @php
        $row_index = $loop->index + $index;
    @endphp
    <tr>
        <td>
            {{$product->product_name}}

            @if($product->variation_name != "DUMMY")
                <b>{{$product->variation_name}}</b>
            @endif
            <input type="hidden" name="products[{{$loop->index + $index}}][product_id]" value="{{$product->product_id}}">
            <input type="hidden" name="products[{{$loop->index + $index}}][variation_id]" value="{{$product->variation_id}}">
        </td>
        <td>
            <input type="number" class="form-control" min=1
            name="products[{{$loop->index + $index}}][quantity]" value="@if(isset($product->quantity)){{$product->quantity}}@else{{1}}@endif">
        </td>
        @if(request()->session()->get('business.enable_lot_number') == 1)
            <td>
                <input type="text" class="form-control"
                name="products[{{$loop->index + $index}}][lot_number]" value="@if(isset($product->lot_number)){{$product->lot_number}}@endif">
            </td>
        @endif
        @if(request()->session()->get('business.enable_product_expiry') == 1)
            <td>
                <input type="text" class="form-control label-date-picker"
                name="products[{{$loop->index + $index}}][exp_date]" value="@if(isset($product->exp_date)){{@format_date($product->exp_date)}}@endif">
            </td>
        @endif
        <td>
            <input type="text" class="form-control label-date-picker"
            name="products[{{$loop->index + $index}}][packing_date]" value="">
        </td>
        <td>
            {!! Form::select('products[' . $row_index . '][price_group_id]', $price_groups, null, ['class' => 'form-control', 'placeholder' => __('lang_v1.none')]); !!}
        </td>
    </tr>
@empty

@endforelse