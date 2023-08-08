<div class="row">
    <div class="col-sm-12">
        <h4>@lang('lang_v1.weighing_scale_setting'):</h4>
        <p>@lang('lang_v1.weighing_scale_setting_help')</p>
        <br/>
    </div>

    <!-- 1st part: Prefix (here any prefix can be entered), user can leave it blank also if prefix not supported by scale.
	2nd part: Dropdown list from 1 to 9 for Barcode 0
	3rd part: Dropdown list from 1 to 5 for Quantity 
	4th part: Dropdown list from 1 to 4 for Quantity decimals. -->


    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('label_prefix', __('lang_v1.weighing_barcode_prefix') . ':') !!}
             {!! Form::text('weighing_scale_setting[label_prefix]', isset($weighing_scale_setting['label_prefix']) ? $weighing_scale_setting['label_prefix'] : null, ['class' => 'form-control', 'id' => 'label_prefix']); !!}
        </div>
    </div>

    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('product_sku_length', __('lang_v1.weighing_product_sku_length') . ':') !!}
            
            {!! Form::select('weighing_scale_setting[product_sku_length]', [1,2,3,4,5,6,7,8,9], isset($weighing_scale_setting['product_sku_length']) ? $weighing_scale_setting['product_sku_length'] : 4, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'product_sku_length']); !!}
        </div>
    </div>

    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('qty_length', __('lang_v1.weighing_qty_integer_part_length') . ':') !!}
            
            {!! Form::select('weighing_scale_setting[qty_length]', [1,2,3,4,5], isset($weighing_scale_setting['qty_length']) ? $weighing_scale_setting['qty_length'] : 3, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'qty_length']); !!}
        </div>
    </div>

    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('qty_length_decimal', __('lang_v1.weighing_qty_fractional_part_length') . ':') !!}
            {!! Form::select('weighing_scale_setting[qty_length_decimal]', [1,2,3,4], isset($weighing_scale_setting['qty_length_decimal']) ? $weighing_scale_setting['qty_length_decimal'] : 2, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'qty_length_decimal']); !!}
        </div>
    </div>
</div>