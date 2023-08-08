<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('default_sales_discount', __('business.default_sales_discount') . ':*') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-percent"></i>
                    </span>
                    {!! Form::text('default_sales_discount', @num_format($business->default_sales_discount), ['class' => 'form-control input_number']); !!}
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('default_sales_tax', __('business.default_sales_tax') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::select('default_sales_tax', $tax_rates, $business->default_sales_tax, ['class' => 'form-control select2','placeholder' => __('business.default_sales_tax'), 'style' => 'width: 100%;']); !!}
                </div>
            </div>
        </div>
        <!-- <div class="clearfix"></div> -->

        {{--<div class="col-sm-12 hide">
            <div class="form-group">
                {!! Form::label('sell_price_tax', __('business.sell_price_tax') . ':') !!}
                <div class="input-group">
                    <div class="radio">
                        <label>
                            <input type="radio" name="sell_price_tax" value="includes" 
                            class="input-icheck" @if($business->sell_price_tax == 'includes') {{'checked'}} @endif> Includes the Sale Tax
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="sell_price_tax" value="excludes" 
                            class="input-icheck" @if($business->sell_price_tax == 'excludes') {{'checked'}} @endif>Excludes the Sale Tax (Calculate sale tax on Selling Price provided in Add Purchase)
                        </label>
                    </div>
                </div>
            </div>
        </div>--}}
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('item_addition_method', __('lang_v1.sales_item_addition_method') . ':') !!}
                {!! Form::select('item_addition_method', [ 0 => __('lang_v1.add_item_in_new_row'), 1 =>  __('lang_v1.increase_item_qty')], $business->item_addition_method, ['class' => 'form-control select2', 'style' => 'width: 100%;']); !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('amount_rounding_method', __('lang_v1.amount_rounding_method') . ':') !!} @show_tooltip(__('lang_v1.amount_rounding_method_help'))
                {!! Form::select('pos_settings[amount_rounding_method]', 
                [ 
                    '1' =>  __('lang_v1.round_to_nearest_whole_number'), 
                    '0.05' =>  __('lang_v1.round_to_nearest_decimal', ['multiple' => 0.05]), 
                    '0.1' =>  __('lang_v1.round_to_nearest_decimal', ['multiple' => 0.1]),
                    '0.5' =>  __('lang_v1.round_to_nearest_decimal', ['multiple' => 0.5])
                ], 
                !empty($pos_settings['amount_rounding_method']) ? $pos_settings['amount_rounding_method'] : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.none')]); !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[enable_msp]', 1,  
                        !empty($pos_settings['enable_msp']) ? true : false , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.sale_price_is_minimum_sale_price' ) }} 
                  </label>
                  @show_tooltip(__('lang_v1.minimum_sale_price_help'))
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[allow_overselling]', 1,  
                        !empty($pos_settings['allow_overselling']) ? true : false , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.allow_overselling' ) }} 
                  </label>
                  @show_tooltip(__('lang_v1.allow_overselling_help'))
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    {!! Form::checkbox('pos_settings[enable_sales_order]', 1, !empty($pos_settings['enable_sales_order']) , [ 'class' => 'input-icheck', 'id' => 'enable_sales_order']); !!} {{ __( 'lang_v1.enable_sales_order' ) }}
                    </label>
                  @show_tooltip(__('lang_v1.sales_order_help_text'))
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    {!! Form::checkbox('pos_settings[is_pay_term_required]', 1, !empty($pos_settings['is_pay_term_required']) , [ 'class' => 'input-icheck', 'id' => 'is_pay_term_required']); !!} {{ __( 'lang_v1.is_pay_term_required' ) }}
                    </label>
                </div>
            </div>
        </div>

    </div>
    <hr>
    <div class="row">
        <div class="col-md-12"><h4>@lang('lang_v1.commission_agent'):</h4></div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('sales_cmsn_agnt', __('lang_v1.sales_commission_agent') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::select('sales_cmsn_agnt', $commission_agent_dropdown, $business->sales_cmsn_agnt, ['class' => 'form-control select2', 'style' => 'width: 100%;']); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('cmmsn_calculation_type', __('lang_v1.cmmsn_calculation_type') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::select('pos_settings[cmmsn_calculation_type]', ['invoice_value' => __('lang_v1.invoice_value'), 'payment_received' => __('lang_v1.payment_received')], !empty($pos_settings['cmmsn_calculation_type']) ? $pos_settings['cmmsn_calculation_type'] : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'cmmsn_calculation_type']); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    {!! Form::checkbox('pos_settings[is_commission_agent_required]', 1, !empty($pos_settings['is_commission_agent_required']) , [ 'class' => 'input-icheck', 'id' => 'is_commission_agent_required']); !!} {{ __( 'lang_v1.is_commission_agent_required' ) }}
                    </label>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12"><h4>@lang('lang_v1.payment_link') @show_tooltip(__('lang_v1.payment_link_help_text')):</h4></div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                    {!! Form::checkbox('pos_settings[enable_payment_link]', 1, !empty($pos_settings['enable_payment_link']) , [ 'class' => 'input-icheck', 'id' => 'enable_payment_link']); !!} {{ __( 'lang_v1.enable_payment_link' ) }}
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <h4>Razorpay: <small>(For INR India)</small></h4>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('razor_pay_key_id', 'Key ID:') !!}
                {!! Form::text('pos_settings[razor_pay_key_id]', $pos_settings['razor_pay_key_id'] ?? '', ['class' => 'form-control', 'id' => 'razor_pay_key_id']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('razor_pay_key_secret', 'Key Secret:') !!}
                {!! Form::text('pos_settings[razor_pay_key_secret]', $pos_settings['razor_pay_key_secret'] ?? '', ['class' => 'form-control', 'id' => 'razor_pay_key_secret']); !!}
            </div>
        </div>

        <div class="col-md-12">
            <h4>Stripe:</h4>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('stripe_public_key', __('lang_v1.stripe_public_key') . ':') !!}
                {!! Form::text('pos_settings[stripe_public_key]', $pos_settings['stripe_public_key'] ?? '', ['class' => 'form-control', 'id' => 'stripe_public_key']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('stripe_secret_key', __('lang_v1.stripe_secret_key') . ':') !!}
                {!! Form::text('pos_settings[stripe_secret_key]', $pos_settings['stripe_secret_key'] ?? '', ['class' => 'form-control', 'id' => 'stripe_secret_key']); !!}
            </div>
        </div>
    </div>
</div>