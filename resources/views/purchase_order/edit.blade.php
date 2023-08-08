@extends('layouts.app')
@section('title', __('lang_v1.edit_purchase_order'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.edit_purchase_order') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
</section>

<!-- Main content -->
<section class="content">

  <!-- Page level currency setting -->
  <input type="hidden" id="p_code" value="{{$currency_details->code}}">
  <input type="hidden" id="p_symbol" value="{{$currency_details->symbol}}">
  <input type="hidden" id="p_thousand" value="{{$currency_details->thousand_separator}}">
  <input type="hidden" id="p_decimal" value="{{$currency_details->decimal_separator}}">

  @include('layouts.partials.error')

  {!! Form::open(['url' =>  action('PurchaseOrderController@update' , [$purchase->id] ), 'method' => 'PUT', 'id' => 'add_purchase_form', 'files' => true ]) !!}

  @php
    $currency_precision = config('constants.currency_precision', 2);
  @endphp
  <input type="hidden" id="is_purchase_order">
  <input type="hidden" id="purchase_id" value="{{ $purchase->id }}">

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
              <div class="form-group">
                {!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-user"></i>
                  </span>
                  {!! Form::select('contact_id', [ $purchase->contact_id => $purchase->contact->name], $purchase->contact_id, ['class' => 'form-control', 'placeholder' => __('messages.please_select') , 'required', 'id' => 'supplier_id']); !!}
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                  </span>
                </div>
              </div>
              <strong>
                @lang('business.address'):
              </strong>
              <div id="supplier_address_div">
                {!! $purchase->contact->contact_address !!}
              </div>
            </div>

            <div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
              <div class="form-group">
                {!! Form::label('ref_no', __('purchase.ref_no') . '*') !!}
                {!! Form::text('ref_no', $purchase->ref_no, ['class' => 'form-control', 'required']); !!}
              </div>
            </div>
            
            <div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-3 @endif">
              <div class="form-group">
                {!! Form::label('transaction_date', __('lang_v1.order_date') . ':*') !!}
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </span>
                  {!! Form::text('transaction_date', @format_datetime($purchase->transaction_date), ['class' => 'form-control', 'readonly', 'required']); !!}
                </div>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('location_id', __('purchase.business_location').':*') !!}
                @show_tooltip(__('tooltip.purchase_location'))
                {!! Form::select('location_id', $business_locations, $purchase->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'disabled']); !!}
              </div>
            </div>

            <!-- Currency Exchange Rate -->
            <div class="col-sm-3 @if(!$currency_details->purchase_in_diff_currency) hide @endif">
              <div class="form-group">
                {!! Form::label('exchange_rate', __('purchase.p_exchange_rate') . ':*') !!}
                @show_tooltip(__('tooltip.currency_exchange_factor'))
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                  </span>
                  {!! Form::number('exchange_rate', $purchase->exchange_rate, ['class' => 'form-control', 'required', 'step' => 0.001]); !!}
                </div>
                <span class="help-block text-danger">
                  @lang('purchase.diff_purchase_currency_help', ['currency' => $currency_details->name])
                </span>
              </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                  <div class="multi-input">
                    {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                    <br/>
                    {!! Form::number('pay_term_number', $purchase->pay_term_number, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}

                    {!! Form::select('pay_term_type', 
                      ['months' => __('lang_v1.months'), 
                        'days' => __('lang_v1.days')], 
                        $purchase->pay_term_type, 
                      ['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select'), 'id' => 'pay_term_type']); !!}
                  </div>
              </div>
          </div>

            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                    {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                    <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                    @includeIf('components.document_help_text')</p>
                </div>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-search"></i>
                  </span>
                  {!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'), 'autofocus']); !!}
                </div>
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <button tabindex="-1" type="button" class="btn btn-link btn-modal"data-href="{{action('ProductController@quickAdd')}}" 
                      data-container=".quick_add_product_modal"><i class="fa fa-plus"></i> @lang( 'product.add_new_product' ) </button>
              </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
              @include('purchase.partials.edit_purchase_entry_row', ['is_purchase_order' => true])

              <hr/>
              <div class="pull-right col-md-5">
                <table class="pull-right col-md-12">
                  <tr>
                    <th class="col-md-7 text-right">@lang( 'lang_v1.total_items' ):</th>
                    <td class="col-md-5 text-left">
                      <span id="total_quantity" class="display_currency" data-currency_symbol="false"></span>
                    </td>
                  </tr>
                  <tr class="hide">
                    <th class="col-md-7 text-right">@lang( 'purchase.total_before_tax' ):</th>
                    <td class="col-md-5 text-left">
                      <span id="total_st_before_tax" class="display_currency"></span>
                      <input type="hidden" id="st_before_tax_input" value=0>
                    </td>
                  </tr>
                  <tr>
                    <th class="col-md-7 text-right">@lang( 'purchase.net_total_amount' ):</th>
                    <td class="col-md-5 text-left">
                      <span id="total_subtotal" class="display_currency">{{$purchase->total_before_tax/$purchase->exchange_rate}}</span>
                      <!-- This is total before purchase tax-->
                      <input type="hidden" id="total_subtotal_input" value="{{$purchase->total_before_tax/$purchase->exchange_rate}}" name="total_before_tax">
                    </td>
                  </tr>
                </table>
              </div>

            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-solid'])
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
                  {!! Form::label('shipping_details', __('sale.shipping_details')) !!}
                  {!! Form::textarea('shipping_details',$purchase->shipping_details, ['class' => 'form-control','placeholder' => __('sale.shipping_details') ,'rows' => '3', 'cols'=>'30']); !!}
              </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
                  {!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
                  {!! Form::textarea('shipping_address',$purchase->shipping_address, ['class' => 'form-control','placeholder' => __('lang_v1.shipping_address') ,'rows' => '3', 'cols'=>'30']); !!}
              </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!!Form::label('shipping_charges', __('sale.shipping_charges'))!!}
            <div class="input-group">
            <span class="input-group-addon">
            <i class="fa fa-info"></i>
            </span>
            {!!Form::text('shipping_charges',number_format($purchase->shipping_charges/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator),['class'=>'form-control input_number','placeholder'=> __('sale.shipping_charges')]);!!}
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-4">
          <div class="form-group">
                  {!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
                  {!! Form::select('shipping_status',$shipping_statuses, $purchase->shipping_status, ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
              </div>
        </div>
        <div class="col-md-4">
              <div class="form-group">
                  {!! Form::label('delivered_to', __('lang_v1.delivered_to') . ':' ) !!}
                  {!! Form::text('delivered_to', $purchase->delivered_to, ['class' => 'form-control','placeholder' => __('lang_v1.delivered_to')]); !!}
              </div>
          </div>
          @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
              $shipping_custom_label_1 = !empty($custom_labels['shipping']['custom_field_1']) ? $custom_labels['shipping']['custom_field_1'] : '';

              $is_shipping_custom_field_1_required = !empty($custom_labels['shipping']['is_custom_field_1_required']) && $custom_labels['shipping']['is_custom_field_1_required'] == 1 ? true : false;

              $shipping_custom_label_2 = !empty($custom_labels['shipping']['custom_field_2']) ? $custom_labels['shipping']['custom_field_2'] : '';

              $is_shipping_custom_field_2_required = !empty($custom_labels['shipping']['is_custom_field_2_required']) && $custom_labels['shipping']['is_custom_field_2_required'] == 1 ? true : false;

              $shipping_custom_label_3 = !empty($custom_labels['shipping']['custom_field_3']) ? $custom_labels['shipping']['custom_field_3'] : '';
              
              $is_shipping_custom_field_3_required = !empty($custom_labels['shipping']['is_custom_field_3_required']) && $custom_labels['shipping']['is_custom_field_3_required'] == 1 ? true : false;

              $shipping_custom_label_4 = !empty($custom_labels['shipping']['custom_field_4']) ? $custom_labels['shipping']['custom_field_4'] : '';
              
              $is_shipping_custom_field_4_required = !empty($custom_labels['shipping']['is_custom_field_4_required']) && $custom_labels['shipping']['is_custom_field_4_required'] == 1 ? true : false;

              $shipping_custom_label_5 = !empty($custom_labels['shipping']['custom_field_5']) ? $custom_labels['shipping']['custom_field_5'] : '';
              
              $is_shipping_custom_field_5_required = !empty($custom_labels['shipping']['is_custom_field_5_required']) && $custom_labels['shipping']['is_custom_field_5_required'] == 1 ? true : false;
            @endphp

            @if(!empty($shipping_custom_label_1))
              @php
                $label_1 = $shipping_custom_label_1 . ':';
                if($is_shipping_custom_field_1_required) {
                  $label_1 .= '*';
                }
              @endphp

              <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_1', $label_1 ) !!}
                    {!! Form::text('shipping_custom_field_1', $purchase->shipping_custom_field_1, ['class' => 'form-control','placeholder' => $shipping_custom_label_1, 'required' => $is_shipping_custom_field_1_required]); !!}
                </div>
            </div>
            @endif
            @if(!empty($shipping_custom_label_2))
              @php
                $label_2 = $shipping_custom_label_2 . ':';
                if($is_shipping_custom_field_2_required) {
                  $label_2 .= '*';
                }
              @endphp

              <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_2', $label_2 ) !!}
                    {!! Form::text('shipping_custom_field_2', $purchase->shipping_custom_field_2, ['class' => 'form-control','placeholder' => $shipping_custom_label_2, 'required' => $is_shipping_custom_field_2_required]); !!}
                </div>
            </div>
            @endif
            @if(!empty($shipping_custom_label_3))
              @php
                $label_3 = $shipping_custom_label_3 . ':';
                if($is_shipping_custom_field_3_required) {
                  $label_3 .= '*';
                }
              @endphp

              <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_3', $label_3 ) !!}
                    {!! Form::text('shipping_custom_field_3', $purchase->shipping_custom_field_3, ['class' => 'form-control','placeholder' => $shipping_custom_label_3, 'required' => $is_shipping_custom_field_3_required]); !!}
                </div>
            </div>
            @endif
            @if(!empty($shipping_custom_label_4))
              @php
                $label_4 = $shipping_custom_label_4 . ':';
                if($is_shipping_custom_field_4_required) {
                  $label_4 .= '*';
                }
              @endphp

              <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_4', $label_4 ) !!}
                    {!! Form::text('shipping_custom_field_4', $purchase->shipping_custom_field_4, ['class' => 'form-control','placeholder' => $shipping_custom_label_4, 'required' => $is_shipping_custom_field_4_required]); !!}
                </div>
            </div>
            @endif
            @if(!empty($shipping_custom_label_5))
              @php
                $label_5 = $shipping_custom_label_5 . ':';
                if($is_shipping_custom_field_5_required) {
                  $label_5 .= '*';
                }
              @endphp

              <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_custom_field_5', $label_5 ) !!}
                    {!! Form::text('shipping_custom_field_5', $purchase->shipping_custom_field_4, ['class' => 'form-control','placeholder' => $shipping_custom_label_5, 'required' => $is_shipping_custom_field_5_required]); !!}
                </div>
            </div>
            @endif
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_documents', __('lang_v1.shipping_documents') . ':') !!}
                    {!! Form::file('shipping_documents[]', ['id' => 'shipping_documents', 'multiple', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                    <p class="help-block">
                      @lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                      @includeIf('components.document_help_text')
                    </p>
                    @php
                        $medias = $purchase->media->where('model_media_type', 'shipping_document')->all();
                    @endphp
                    @include('sell.partials.media_table', ['medias' => $medias, 'delete' => true])
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 text-center">
              <button type="button" class="btn btn-primary btn-sm" id="toggle_additional_expense"> <i class="fas fa-plus"></i> @lang('lang_v1.add_additional_expenses') <i class="fas fa-chevron-down"></i></button>
            </div>
            <div class="col-md-8 col-md-offset-4" id="additional_expenses_div">
              <table class="table table-condensed">
                <thead>
                  <tr>
                    <th>@lang('lang_v1.additional_expense_name')</th>
                    <th>@lang('sale.amount')</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      {!! Form::text('additional_expense_key_1', $purchase->additional_expense_key_1, ['class' => 'form-control']); !!}
                    </td>
                    <td>
                      {!! Form::text('additional_expense_value_1', number_format($purchase->additional_expense_value_1/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_1']); !!}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {!! Form::text('additional_expense_key_2', $purchase->additional_expense_key_2, ['class' => 'form-control']); !!}
                    </td>
                    <td>
                      {!! Form::text('additional_expense_value_2', number_format($purchase->additional_expense_value_2/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_2']); !!}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {!! Form::text('additional_expense_key_3', $purchase->additional_expense_key_3, ['class' => 'form-control']); !!}
                    </td>
                    <td>
                      {!! Form::text('additional_expense_value_3', number_format($purchase->additional_expense_value_3/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_3']); !!}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {!! Form::text('additional_expense_key_4', $purchase->additional_expense_key_4, ['class' => 'form-control']); !!}
                    </td>
                    <td>
                      {!! Form::text('additional_expense_value_4', number_format($purchase->additional_expense_value_4/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input_number', 'id' => 'additional_expense_value_4']); !!}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 col-md-offset-8">
                {!! Form::hidden('final_total', $purchase->final_total , ['id' => 'grand_total_hidden']); !!}
                      <b>@lang('lang_v1.order_total'): </b><span id="grand_total" class="display_currency" data-currency_symbol='true'>{{$purchase->final_total}}</span>
            </div>
          </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                  <tr class="hide">
                    <td class="col-md-3">
                      <div class="form-group">
                        {!! Form::label('discount_type', __( 'purchase.discount_type' ) . ':') !!}
                        {!! Form::select('discount_type', [ '' => __('lang_v1.none'), 'fixed' => __( 'lang_v1.fixed' ), 'percentage' => __( 'lang_v1.percentage' )], $purchase->discount_type, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                      </div>
                    </td>
                    <td class="col-md-3">
                      <div class="form-group">
                      {!! Form::label('discount_amount', __( 'purchase.discount_amount' ) . ':') !!}
                      {!! Form::text('discount_amount', 

                      ($purchase->discount_type == 'fixed' ? 
                        number_format($purchase->discount_amount/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)
                      :
                        number_format($purchase->discount_amount, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)
                      )
                      , ['class' => 'form-control input_number']); !!}
                      </div>
                    </td>
                    <td class="col-md-3">
                      &nbsp;
                    </td>
                    <td class="col-md-3">
                      <b>Discount:</b>(-) 
                      <span id="discount_calculated_amount" class="display_currency">0</span>
                    </td>
                  </tr>
                  <tr class="hide">
                    <td>
                      <div class="form-group">
                      {!! Form::label('tax_id', __( 'purchase.purchase_tax' ) . ':') !!}
                      <select name="tax_id" id="tax_id" class="form-control select2" placeholder="'Please Select'">
                        <option value="" data-tax_amount="0" selected>@lang('lang_v1.none')</option>
                        @foreach($taxes as $tax)
                          <option value="{{ $tax->id }}" @if($purchase->tax_id == $tax->id) {{'selected'}} @endif data-tax_amount="{{ $tax->amount }}"
                          >
                            {{ $tax->name }}
                          </option>
                        @endforeach
                      </select>
                      {!! Form::hidden('tax_amount', $purchase->tax_amount, ['id' => 'tax_amount']); !!}
                      </div>
                    </td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>
                      <b>@lang( 'purchase.purchase_tax' ):</b>(+) 
                      <span id="tax_calculated_amount" class="display_currency">0</span>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="4">
                      <div class="form-group">
                        {!! Form::label('additional_notes',__('purchase.additional_notes')) !!}
                        {!! Form::textarea('additional_notes', $purchase->additional_notes, ['class' => 'form-control', 'rows' => 3]); !!}
                      </div>
                    </td>
                  </tr>

                </table>
            </div>
        </div>
    @endcomponent
  
    <div class="row">
        <div class="col-sm-12">
          <button type="button" id="submit_purchase_form" class="btn btn-primary pull-right btn-flat">@lang('messages.update')</button>
        </div>
    </div>
{!! Form::close() !!}
</section>
<!-- /.content -->
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
  @include('contact.create', ['quick_add' => true])
</div>

@endsection

@section('javascript')
  <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
  <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
  <script type="text/javascript">
    $(document).ready( function(){
      update_table_total();
      update_grand_total();
      __page_leave_confirmation('#add_purchase_form');
        $('#shipping_documents').fileinput({
            showUpload: false,
            showPreview: false,
            browseLabel: LANG.file_browse_label,
            removeLabel: LANG.remove,
        });
    });
  </script>
  @include('purchase.partials.keyboard_shortcuts')
@endsection
