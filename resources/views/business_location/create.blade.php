<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('BusinessLocationController@store'), 'method' => 'post', 'id' => 'business_location_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'business.add_business_location' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('name', __( 'invoice.name' ) . ':*') !!}
              {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'invoice.name' ) ]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('location_id', __( 'lang_v1.location_id' ) . ':') !!}
              {!! Form::text('location_id', null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.location_id' ) ]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('landmark', __( 'business.landmark' ) . ':') !!}
              {!! Form::text('landmark', null, ['class' => 'form-control', 'placeholder' => __( 'business.landmark' ) ]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('city', __( 'business.city' ) . ':*') !!}
              {!! Form::text('city', null, ['class' => 'form-control', 'placeholder' => __( 'business.city'), 'required' ]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('zip_code', __( 'business.zip_code' ) . ':*') !!}
              {!! Form::text('zip_code', null, ['class' => 'form-control', 'placeholder' => __( 'business.zip_code'), 'required' ]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('state', __( 'business.state' ) . ':*') !!}
              {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __( 'business.state'), 'required' ]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('country', __( 'business.country' ) . ':*') !!}
              {!! Form::text('country', null, ['class' => 'form-control', 'placeholder' => __( 'business.country'), 'required' ]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('mobile', __( 'business.mobile' ) . ':') !!}
            {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => __( 'business.mobile')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('alternate_number', __( 'business.alternate_number' ) . ':') !!}
            {!! Form::text('alternate_number', null, ['class' => 'form-control', 'placeholder' => __( 'business.alternate_number')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('email', __( 'business.email' ) . ':') !!}
            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __( 'business.email')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('website', __( 'lang_v1.website' ) . ':') !!}
            {!! Form::text('website', null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.website')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':*') !!} @show_tooltip(__('tooltip.invoice_scheme'))
              {!! Form::select('invoice_scheme_id', $invoice_schemes, null, ['class' => 'form-control', 'required',
              'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('invoice_layout_id', __('lang_v1.invoice_layout_for_pos') . ':*') !!} @show_tooltip(__('tooltip.invoice_layout'))
              {!! Form::select('invoice_layout_id', $invoice_layouts, null, ['class' => 'form-control', 'required',
              'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('sale_invoice_layout_id', __('lang_v1.invoice_layout_for_sale') . ':*') !!} @show_tooltip(__('lang_v1.invoice_layout_for_sale_tooltip'))
              {!! Form::select('sale_invoice_layout_id', $invoice_layouts, null, ['class' => 'form-control', 'required',
              'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('selling_price_group_id', __('lang_v1.default_selling_price_group') . ':') !!} @show_tooltip(__('lang_v1.location_price_group_help'))
              {!! Form::select('selling_price_group_id', $price_groups, null, ['class' => 'form-control',
              'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        @php
          $custom_labels = json_decode(session('business.custom_labels'), true);
          $location_custom_field1 = !empty($custom_labels['location']['custom_field_1']) ? $custom_labels['location']['custom_field_1'] : __('lang_v1.location_custom_field1');
          $location_custom_field2 = !empty($custom_labels['location']['custom_field_2']) ? $custom_labels['location']['custom_field_2'] : __('lang_v1.location_custom_field2');
          $location_custom_field3 = !empty($custom_labels['location']['custom_field_3']) ? $custom_labels['location']['custom_field_3'] : __('lang_v1.location_custom_field3');
          $location_custom_field4 = !empty($custom_labels['location']['custom_field_4']) ? $custom_labels['location']['custom_field_4'] : __('lang_v1.location_custom_field4');
        @endphp
        <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('custom_field1', $location_custom_field1 . ':') !!}
            {!! Form::text('custom_field1', null, ['class' => 'form-control', 
                'placeholder' => $location_custom_field1]); !!}
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('custom_field2', $location_custom_field2 . ':') !!}
            {!! Form::text('custom_field2', null, ['class' => 'form-control', 
                'placeholder' => $location_custom_field2]); !!}
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('custom_field3', $location_custom_field3 . ':') !!}
            {!! Form::text('custom_field3', null, ['class' => 'form-control', 
                'placeholder' => $location_custom_field3]); !!}
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('custom_field4', $location_custom_field4 . ':') !!}
            {!! Form::text('custom_field4', null, ['class' => 'form-control', 
                'placeholder' => $location_custom_field4]); !!}
        </div>
      </div>
      <div class="clearfix"></div>
      <hr>
      <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('featured_products', __('lang_v1.pos_screen_featured_products') . ':') !!} @show_tooltip(__('lang_v1.featured_products_help'))
              {!! Form::select('featured_products[]', [], null, ['class' => 'form-control',
              'id' => 'featured_products', 'multiple']); !!}
          </div>
        </div>
      <div class="clearfix"></div>
        <hr>
          <div class="col-sm-12">
            <strong>@lang('lang_v1.payment_options'): @show_tooltip(__('lang_v1.payment_option_help'))</strong>
            <div class="form-group">
            <table class="table table-condensed table-striped">
              <thead>
                <tr>
                  <th class="text-center">@lang('lang_v1.payment_method')</th>
                  <th class="text-center">@lang('lang_v1.enable')</th>
                  <th class="text-center @if(empty($accounts)) hide @endif">@lang('lang_v1.default_accounts') @show_tooltip(__('lang_v1.default_account_help'))</th>
                </tr>
              </thead>
              <tbody>
                @foreach($payment_types as $key => $value)
                  <tr>
                    <td class="text-center">{{$value}}</td>
                    <td class="text-center">{!! Form::checkbox('default_payment_accounts[' . $key . '][is_enabled]', 1, true); !!}</td>
                    <td class="text-center @if(empty($accounts)) hide @endif">
                      {!! Form::select('default_payment_accounts[' . $key . '][account]', $accounts, null, ['class' => 'form-control input-sm']); !!}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            </div>
          </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->