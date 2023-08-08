<div class="pos-tab-content">
     <div class="row">
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_custom_payments'):</h4>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('custom_payment_1_label', __('lang_v1.custom_payment_1')); !!}
                {!! Form::text('custom_labels[payments][custom_pay_1]', !empty($custom_labels['payments']['custom_pay_1']) ? $custom_labels['payments']['custom_pay_1'] : null, 
                    ['class' => 'form-control', 'id' => 'custom_payment_1']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('custom_payment_2_label', __('lang_v1.custom_payment_2')); !!}
                {!! Form::text('custom_labels[payments][custom_pay_2]', !empty($custom_labels['payments']['custom_pay_2']) ? $custom_labels['payments']['custom_pay_2'] : null, 
                    ['class' => 'form-control', 'id' => 'custom_payment_2']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('custom_payment_3_label', __('lang_v1.custom_payment_3')); !!}
                {!! Form::text('custom_labels[payments][custom_pay_3]', !empty($custom_labels['payments']['custom_pay_3']) ? $custom_labels['payments']['custom_pay_3'] : null, 
                    ['class' => 'form-control', 'id' => 'custom_payment_3']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('custom_payment_4_label', __('lang_v1.custom_payment', ['number' => 4])); !!}
                {!! Form::text('custom_labels[payments][custom_pay_4]', !empty($custom_labels['payments']['custom_pay_4']) ? $custom_labels['payments']['custom_pay_4'] : null, 
                    ['class' => 'form-control', 'id' => 'custom_payment_4']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('custom_payment_5_label', __('lang_v1.custom_payment', ['number' => 5])); !!}
                {!! Form::text('custom_labels[payments][custom_pay_5]', !empty($custom_labels['payments']['custom_pay_5']) ? $custom_labels['payments']['custom_pay_5'] : null, 
                    ['class' => 'form-control', 'id' => 'custom_payment_5']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('custom_payment_6_label', __('lang_v1.custom_payment', ['number' => 6])); !!}
                {!! Form::text('custom_labels[payments][custom_pay_6]', !empty($custom_labels['payments']['custom_pay_6']) ? $custom_labels['payments']['custom_pay_6'] : null, 
                    ['class' => 'form-control', 'id' => 'custom_payment_6']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('custom_payment_6_label', __('lang_v1.custom_payment', ['number' => 7])); !!}
                {!! Form::text('custom_labels[payments][custom_pay_7]', !empty($custom_labels['payments']['custom_pay_7']) ? $custom_labels['payments']['custom_pay_7'] : null, 
                    ['class' => 'form-control', 'id' => 'custom_payment_7']); !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_contact_custom_fields'):</h4>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_1_label', __('lang_v1.contact_custom_field1')); !!}
                {!! Form::text('custom_labels[contact][custom_field_1]', !empty($custom_labels['contact']['custom_field_1']) ? $custom_labels['contact']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_1_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_2_label', __('lang_v1.contact_custom_field2')); !!}
                {!! Form::text('custom_labels[contact][custom_field_2]', !empty($custom_labels['contact']['custom_field_2']) ? $custom_labels['contact']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_2_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_3_label', __('lang_v1.contact_custom_field3')); !!}
                {!! Form::text('custom_labels[contact][custom_field_3]', !empty($custom_labels['contact']['custom_field_3']) ? $custom_labels['contact']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_3_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_4_label', __('lang_v1.contact_custom_field4')); !!}
                {!! Form::text('custom_labels[contact][custom_field_4]', !empty($custom_labels['contact']['custom_field_4']) ? $custom_labels['contact']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_4_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_5_label', __('lang_v1.custom_field', ['number' => 5])); !!}
                {!! Form::text('custom_labels[contact][custom_field_5]', !empty($custom_labels['contact']['custom_field_5']) ? $custom_labels['contact']['custom_field_5'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_5_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_6_label', __('lang_v1.custom_field', ['number' => 6])); !!}
                {!! Form::text('custom_labels[contact][custom_field_6]', !empty($custom_labels['contact']['custom_field_6']) ? $custom_labels['contact']['custom_field_6'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_6_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_7_label', __('lang_v1.custom_field', ['number' => 7])); !!}
                {!! Form::text('custom_labels[contact][custom_field_7]', !empty($custom_labels['contact']['custom_field_7']) ? $custom_labels['contact']['custom_field_7'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_7_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_8_label', __('lang_v1.custom_field', ['number' => 8])); !!}
                {!! Form::text('custom_labels[contact][custom_field_8]', !empty($custom_labels['contact']['custom_field_8']) ? $custom_labels['contact']['custom_field_8'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_8_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_9_label', __('lang_v1.custom_field', ['number' => 9])); !!}
                {!! Form::text('custom_labels[contact][custom_field_9]', !empty($custom_labels['contact']['custom_field_9']) ? $custom_labels['contact']['custom_field_9'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_9_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('contact_custom_field_10_label', __('lang_v1.custom_field', ['number' => 10])); !!}
                {!! Form::text('custom_labels[contact][custom_field_10]', !empty($custom_labels['contact']['custom_field_10']) ? $custom_labels['contact']['custom_field_10'] : null, 
                    ['class' => 'form-control', 'id' => 'contact_custom_field_10_label']); !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_product_custom_fields'):</h4>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('product_custom_field_1_label', __('lang_v1.product_custom_field1')); !!}
                {!! Form::text('custom_labels[product][custom_field_1]', !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'product_custom_field_1_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('product_custom_field_2_label', __('lang_v1.product_custom_field2')); !!}
                {!! Form::text('custom_labels[product][custom_field_2]', !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'product_custom_field_2_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('product_custom_field_3_label', __('lang_v1.product_custom_field3')); !!}
                {!! Form::text('custom_labels[product][custom_field_3]', !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'product_custom_field_3_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('product_custom_field_4_label', __('lang_v1.product_custom_field4')); !!}
                {!! Form::text('custom_labels[product][custom_field_4]', !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'product_custom_field_4_label']); !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_location_custom_fields'):</h4>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('location_custom_field_1_label', __('lang_v1.location_custom_field1')); !!}
                {!! Form::text('custom_labels[location][custom_field_1]', !empty($custom_labels['location']['custom_field_1']) ? $custom_labels['location']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'location_custom_field_1_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('location_custom_field_2_label', __('lang_v1.location_custom_field2')); !!}
                {!! Form::text('custom_labels[location][custom_field_2]', !empty($custom_labels['location']['custom_field_2']) ? $custom_labels['location']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'location_custom_field_2_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('location_custom_field_3_label', __('lang_v1.location_custom_field3')); !!}
                {!! Form::text('custom_labels[location][custom_field_3]', !empty($custom_labels['location']['custom_field_3']) ? $custom_labels['location']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'location_custom_field_3_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('location_custom_field_4_label', __('lang_v1.location_custom_field4')); !!}
                {!! Form::text('custom_labels[location][custom_field_4]', !empty($custom_labels['location']['custom_field_4']) ? $custom_labels['location']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'location_custom_field_4_label']); !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_user_custom_fields'):</h4>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('user_custom_field_1_label', __('lang_v1.user_custom_field1')); !!}
                {!! Form::text('custom_labels[user][custom_field_1]', !empty($custom_labels['user']['custom_field_1']) ? $custom_labels['user']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'user_custom_field_1_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('user_custom_field_2_label', __('lang_v1.user_custom_field2')); !!}
                {!! Form::text('custom_labels[user][custom_field_2]', !empty($custom_labels['user']['custom_field_2']) ? $custom_labels['user']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'user_custom_field_2_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('user_custom_field_3_label', __('lang_v1.user_custom_field3')); !!}
                {!! Form::text('custom_labels[user][custom_field_3]', !empty($custom_labels['user']['custom_field_3']) ? $custom_labels['user']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'user_custom_field_3_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('user_custom_field_4_label', __('lang_v1.user_custom_field4')); !!}
                {!! Form::text('custom_labels[user][custom_field_4]', !empty($custom_labels['user']['custom_field_4']) ? $custom_labels['user']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'user_custom_field_4_label']); !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_purchase_custom_fields'):</h4>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_custom_field_1_label', __('lang_v1.product_custom_field1')); !!}
                <div class="input-group">
                    {!! Form::text('custom_labels[purchase][custom_field_1]', !empty($custom_labels['purchase']['custom_field_1']) ? $custom_labels['purchase']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_custom_field_1_label']); !!}

                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase][is_custom_field_1_required]" value="1" @if(!empty($custom_labels['purchase']['is_custom_field_1_required']) && $custom_labels['purchase']['is_custom_field_1_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_custom_field_2_label', __('lang_v1.product_custom_field2')); !!}
                <div class="input-group">
                    {!! Form::text('custom_labels[purchase][custom_field_2]', !empty($custom_labels['purchase']['custom_field_2']) ? $custom_labels['purchase']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_custom_field_2_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase][is_custom_field_2_required]" value="1" @if(!empty($custom_labels['purchase']['is_custom_field_2_required']) && $custom_labels['purchase']['is_custom_field_2_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_custom_field_3_label', __('lang_v1.product_custom_field3')); !!}

                <div class="input-group">
                    {!! Form::text('custom_labels[purchase][custom_field_3]', !empty($custom_labels['purchase']['custom_field_3']) ? $custom_labels['purchase']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_custom_field_3_label']); !!}

                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase][is_custom_field_3_required]" value="1" @if(!empty($custom_labels['purchase']['is_custom_field_3_required']) && $custom_labels['purchase']['is_custom_field_3_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_custom_field_4_label', __('lang_v1.product_custom_field4')); !!}
                <div class="input-group">
                    {!! Form::text('custom_labels[purchase][custom_field_4]', !empty($custom_labels['purchase']['custom_field_4']) ? $custom_labels['purchase']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_custom_field_4_label']); !!}

                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase][is_custom_field_4_required]" value="1" @if(!empty($custom_labels['purchase']['is_custom_field_4_required']) && $custom_labels['purchase']['is_custom_field_4_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
                <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_purchase_shipping_custom_fields'):</h4>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_shipping_custom_field_1_label', __('lang_v1.custom_field', ['number' => 1])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[purchase_shipping][custom_field_1]', !empty($custom_labels['purchase_shipping']['custom_field_1']) ? $custom_labels['purchase_shipping']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_shipping_custom_field_1_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase_shipping][is_custom_field_1_required]" value="1" @if(!empty($custom_labels['purchase_shipping']['is_custom_field_1_required']) && $custom_labels['purchase_shipping']['is_custom_field_1_required'] == 1) checked @endif > @lang('lang_v1.is_required')</labe>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_shipping_custom_field_2_label', __('lang_v1.custom_field', ['number' => 2])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[purchase_shipping][custom_field_2]', !empty($custom_labels['purchase_shipping']['custom_field_2']) ? $custom_labels['purchase_shipping']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_shipping_custom_field_2_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase_shipping][is_custom_field_2_required]" value="1" @if(!empty($custom_labels['purchase_shipping']['is_custom_field_2_required']) && $custom_labels['purchase_shipping']['is_custom_field_2_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_shipping_custom_field_3_label', __('lang_v1.custom_field', ['number' => 3])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[purchase_shipping][custom_field_3]', !empty($custom_labels['purchase_shipping']['custom_field_3']) ? $custom_labels['purchase_shipping']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_shipping_custom_field_3_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase_shipping][is_custom_field_3_required]" value="1" @if(!empty($custom_labels['purchase_shipping']['is_custom_field_3_required']) && $custom_labels['purchase_shipping']['is_custom_field_3_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_shipping_custom_field_4_label', __('lang_v1.custom_field', ['number' => 4])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[purchase_shipping][custom_field_4]', !empty($custom_labels['purchase_shipping']['custom_field_4']) ? $custom_labels['purchase_shipping']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_shipping_custom_field_4_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase_shipping][is_custom_field_4_required]" value="1" @if(!empty($custom_labels['purchase_shipping']['is_custom_field_4_required']) && $custom_labels['purchase_shipping']['is_custom_field_4_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('purchase_shipping_custom_field_5_label', __('lang_v1.custom_field', ['number' => 5])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[purchase_shipping][custom_field_5]', !empty($custom_labels['purchase_shipping']['custom_field_5']) ? $custom_labels['purchase_shipping']['custom_field_5'] : null, 
                    ['class' => 'form-control', 'id' => 'purchase_shipping_custom_field_5_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[purchase_shipping][is_custom_field_5_required]" value="1" @if(!empty($custom_labels['purchase_shipping']['is_custom_field_5_required']) && $custom_labels['purchase_shipping']['is_custom_field_5_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_sell_custom_fields'):</h4>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('sell_custom_field_1_label', __('lang_v1.product_custom_field1')); !!}
                <div class="input-group">
                    {!! Form::text('custom_labels[sell][custom_field_1]', !empty($custom_labels['sell']['custom_field_1']) ? $custom_labels['sell']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'sell_custom_field_1_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[sell][is_custom_field_1_required]" value="1" @if(!empty($custom_labels['sell']['is_custom_field_1_required']) && $custom_labels['sell']['is_custom_field_1_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('sell_custom_field_2_label', __('lang_v1.product_custom_field2')); !!}
                <div class="input-group"> 
                {!! Form::text('custom_labels[sell][custom_field_2]', !empty($custom_labels['sell']['custom_field_2']) ? $custom_labels['sell']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'sell_custom_field_2_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[sell][is_custom_field_2_required]" value="1" @if(!empty($custom_labels['sell']['is_custom_field_2_required']) && $custom_labels['sell']['is_custom_field_2_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('sell_custom_field_3_label', __('lang_v1.product_custom_field3')); !!}
                <div class="input-group">
                    {!! Form::text('custom_labels[sell][custom_field_3]', !empty($custom_labels['sell']['custom_field_3']) ? $custom_labels['sell']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'sell_custom_field_3_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[sell][is_custom_field_3_required]" value="1" @if(!empty($custom_labels['sell']['is_custom_field_3_required']) && $custom_labels['sell']['is_custom_field_3_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('sell_custom_field_4_label', __('lang_v1.product_custom_field4')); !!}
                <div class="input-group">
                    {!! Form::text('custom_labels[sell][custom_field_4]', !empty($custom_labels['sell']['custom_field_4']) ? $custom_labels['sell']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'sell_custom_field_4_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[sell][is_custom_field_4_required]" value="1" @if(!empty($custom_labels['sell']['is_custom_field_4_required']) && $custom_labels['sell']['is_custom_field_4_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_sale_shipping_custom_fields'):</h4>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('shipping_custom_field_1_label', __('lang_v1.custom_field', ['number' => 1])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[shipping][custom_field_1]', !empty($custom_labels['shipping']['custom_field_1']) ? $custom_labels['shipping']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'shipping_custom_field_1_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[shipping][is_custom_field_1_required]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_1_required']) && $custom_labels['shipping']['is_custom_field_1_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                        &nbsp;
                        <label>
                            <input type="checkbox" name="custom_labels[shipping][is_custom_field_1_contact_default]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_1_contact_default']) && $custom_labels['shipping']['is_custom_field_1_contact_default'] == 1) checked @endif > @lang('lang_v1.is_default_for_contact')
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('shipping_custom_field_2_label', __('lang_v1.custom_field', ['number' => 2])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[shipping][custom_field_2]', !empty($custom_labels['shipping']['custom_field_2']) ? $custom_labels['shipping']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'shipping_custom_field_2_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[shipping][is_custom_field_2_required]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_2_required']) && $custom_labels['shipping']['is_custom_field_2_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                        &nbsp;
                        <label>
                            <input type="checkbox" name="custom_labels[shipping][is_custom_field_2_contact_default]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_2_contact_default']) && $custom_labels['shipping']['is_custom_field_2_contact_default'] == 1) checked @endif > @lang('lang_v1.is_default_for_contact')
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('shipping_custom_field_3_label', __('lang_v1.custom_field', ['number' => 3])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[shipping][custom_field_3]', !empty($custom_labels['shipping']['custom_field_3']) ? $custom_labels['shipping']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'shipping_custom_field_3_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[shipping][is_custom_field_3_required]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_3_required']) && $custom_labels['shipping']['is_custom_field_3_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                        &nbsp;
                        <label>
                            <input type="checkbox" name="custom_labels[shipping][is_custom_field_3_contact_default]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_3_contact_default']) && $custom_labels['shipping']['is_custom_field_3_contact_default'] == 1) checked @endif > @lang('lang_v1.is_default_for_contact')
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('shipping_custom_field_4_label', __('lang_v1.custom_field', ['number' => 4])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[shipping][custom_field_4]', !empty($custom_labels['shipping']['custom_field_4']) ? $custom_labels['shipping']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'shipping_custom_field_4_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[shipping][is_custom_field_4_required]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_4_required']) && $custom_labels['shipping']['is_custom_field_4_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                        &nbsp;
                        <label>
                            <input type="checkbox" name="custom_labels[shipping][is_custom_field_4_contact_default]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_4_contact_default']) && $custom_labels['shipping']['is_custom_field_4_contact_default'] == 1) checked @endif > @lang('lang_v1.is_default_for_contact')
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('shipping_custom_field_5_label', __('lang_v1.custom_field', ['number' => 5])) !!}
                <div class="input-group">
                {!! Form::text('custom_labels[shipping][custom_field_5]', !empty($custom_labels['shipping']['custom_field_5']) ? $custom_labels['shipping']['custom_field_5'] : null, 
                    ['class' => 'form-control', 'id' => 'shipping_custom_field_5_label']); !!}
                    <div class="input-group-addon">
                        <label>
                        <input type="checkbox" name="custom_labels[shipping][is_custom_field_5_required]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_5_required']) && $custom_labels['shipping']['is_custom_field_5_required'] == 1) checked @endif > @lang('lang_v1.is_required')</label>
                        &nbsp;
                        <label>
                            <input type="checkbox" name="custom_labels[shipping][is_custom_field_5_contact_default]" value="1" @if(!empty($custom_labels['shipping']['is_custom_field_5_contact_default']) && $custom_labels['shipping']['is_custom_field_5_contact_default'] == 1) checked @endif > @lang('lang_v1.is_default_for_contact')
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12">
            <h4>@lang('lang_v1.labels_for_types_of_service_custom_fields'):</h4>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('types_of_service_custom_field_1_label', __('lang_v1.service_custom_field_1')); !!}
                {!! Form::text('custom_labels[types_of_service][custom_field_1]', !empty($custom_labels['types_of_service']['custom_field_1']) ? $custom_labels['types_of_service']['custom_field_1'] : null, 
                    ['class' => 'form-control', 'id' => 'types_of_service_custom_field_1_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('types_of_service_custom_field_2_label', __('lang_v1.service_custom_field_2')); !!}
                {!! Form::text('custom_labels[types_of_service][custom_field_2]', !empty($custom_labels['types_of_service']['custom_field_2']) ? $custom_labels['types_of_service']['custom_field_2'] : null, 
                    ['class' => 'form-control', 'id' => 'types_of_service_custom_field_2_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('types_of_service_custom_field_3_label', __('lang_v1.service_custom_field_3')); !!}
                {!! Form::text('custom_labels[types_of_service][custom_field_3]', !empty($custom_labels['types_of_service']['custom_field_3']) ? $custom_labels['types_of_service']['custom_field_3'] : null, 
                    ['class' => 'form-control', 'id' => 'types_of_service_custom_field_3_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('types_of_service_custom_field_4_label', __('lang_v1.service_custom_field_4')); !!}
                {!! Form::text('custom_labels[types_of_service][custom_field_4]', !empty($custom_labels['types_of_service']['custom_field_4']) ? $custom_labels['types_of_service']['custom_field_4'] : null, 
                    ['class' => 'form-control', 'id' => 'types_of_service_custom_field_4_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('types_of_service_custom_field_5_label', __('lang_v1.custom_field', ['number' => 5])); !!}
                {!! Form::text('custom_labels[types_of_service][custom_field_5]', !empty($custom_labels['types_of_service']['custom_field_5']) ? $custom_labels['types_of_service']['custom_field_5'] : null, 
                    ['class' => 'form-control', 'id' => 'types_of_service_custom_field_5_label']); !!}
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('types_of_service_custom_field_6_label', __('lang_v1.custom_field', ['number' => 6])); !!}
                {!! Form::text('custom_labels[types_of_service][custom_field_6]', !empty($custom_labels['types_of_service']['custom_field_6']) ? $custom_labels['types_of_service']['custom_field_6'] : null, 
                    ['class' => 'form-control', 'id' => 'types_of_service_custom_field_6_label']); !!}
            </div>
        </div>
    </div>
</div>