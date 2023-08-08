<div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    @php
      $title = $purchase->type == 'purchase_order' ? __('lang_v1.purchase_order_details') : __('purchase.purchase_details');
      $custom_labels = json_decode(session('business.custom_labels'), true);
    @endphp
    <h4 class="modal-title" id="modalTitle"> {{$title}} (<b>@lang('purchase.ref_no'):</b> #{{ $purchase->ref_no }})
    </h4>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-sm-12">
      <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($purchase->transaction_date) }}</p>
    </div>
  </div>
  <div class="row invoice-info">
    <div class="col-sm-4 invoice-col">
      @lang('purchase.supplier'):
      <address>
        {!! $purchase->contact->contact_address !!}
        @if(!empty($purchase->contact->tax_number))
          <br>@lang('contact.tax_no'): {{$purchase->contact->tax_number}}
        @endif
        @if(!empty($purchase->contact->mobile))
          <br>@lang('contact.mobile'): {{$purchase->contact->mobile}}
        @endif
        @if(!empty($purchase->contact->email))
          <br>@lang('business.email'): {{$purchase->contact->email}}
        @endif
      </address>
      @if($purchase->document_path)
        
        <a href="{{$purchase->document_path}}" 
        download="{{$purchase->document_name}}" class="btn btn-sm btn-success pull-left no-print">
          <i class="fa fa-download"></i> 
            &nbsp;{{ __('purchase.download_document') }}
        </a>
      @endif
    </div>

    <div class="col-sm-4 invoice-col">
      @lang('business.business'):
      <address>
        <strong>{{ $purchase->business->name }}</strong>
        {{ $purchase->location->name }}
        @if(!empty($purchase->location->landmark))
          <br>{{$purchase->location->landmark}}
        @endif
        @if(!empty($purchase->location->city) || !empty($purchase->location->state) || !empty($purchase->location->country))
          <br>{{implode(',', array_filter([$purchase->location->city, $purchase->location->state, $purchase->location->country]))}}
        @endif
        
        @if(!empty($purchase->business->tax_number_1))
          <br>{{$purchase->business->tax_label_1}}: {{$purchase->business->tax_number_1}}
        @endif

        @if(!empty($purchase->business->tax_number_2))
          <br>{{$purchase->business->tax_label_2}}: {{$purchase->business->tax_number_2}}
        @endif

        @if(!empty($purchase->location->mobile))
          <br>@lang('contact.mobile'): {{$purchase->location->mobile}}
        @endif
        @if(!empty($purchase->location->email))
          <br>@lang('business.email'): {{$purchase->location->email}}
        @endif
      </address>
    </div>

    <div class="col-sm-4 invoice-col">
      <b>@lang('purchase.ref_no'):</b> #{{ $purchase->ref_no }}<br/>
      <b>@lang('messages.date'):</b> {{ @format_date($purchase->transaction_date) }}<br/>
      @if(!empty($purchase->status))
        <b>@lang('purchase.purchase_status'):</b> @if($purchase->type == 'purchase_order'){{$po_statuses[$purchase->status]['label'] ?? ''}} @else {{ __('lang_v1.' . $purchase->status) }} @endif<br>
      @endif
      @if(!empty($purchase->payment_status))
      <b>@lang('purchase.payment_status'):</b> {{ __('lang_v1.' . $purchase->payment_status) }}
      @endif

      @if(!empty($custom_labels['purchase']['custom_field_1']))
        <br><strong>{{$custom_labels['purchase']['custom_field_1'] ?? ''}}: </strong> {{$purchase->custom_field_1}}
      @endif
      @if(!empty($custom_labels['purchase']['custom_field_2']))
        <br><strong>{{$custom_labels['purchase']['custom_field_2'] ?? ''}}: </strong> {{$purchase->custom_field_2}}
      @endif
      @if(!empty($custom_labels['purchase']['custom_field_3']))
        <br><strong>{{$custom_labels['purchase']['custom_field_3'] ?? ''}}: </strong> {{$purchase->custom_field_3}}
      @endif
      @if(!empty($custom_labels['purchase']['custom_field_4']))
        <br><strong>{{$custom_labels['purchase']['custom_field_4'] ?? ''}}: </strong> {{$purchase->custom_field_4}}
      @endif
      @if(!empty($purchase_order_nos))
            <strong>@lang('restaurant.order_no'):</strong>
            {{$purchase_order_nos}}
        @endif

        @if(!empty($purchase_order_dates))
            <br>
            <strong>@lang('lang_v1.order_dates'):</strong>
            {{$purchase_order_dates}}
        @endif
      @if($purchase->type == 'purchase_order')
        @php
          $custom_labels = json_decode(session('business.custom_labels'), true);
        @endphp
        <strong>@lang('sale.shipping'):</strong>
        <span class="label @if(!empty($shipping_status_colors[$purchase->shipping_status])) {{$shipping_status_colors[$purchase->shipping_status]}} @else {{'bg-gray'}} @endif">{{$shipping_statuses[$purchase->shipping_status] ?? '' }}</span><br>
        @if(!empty($purchase->shipping_address()))
          {{$purchase->shipping_address()}}
        @else
          {{$purchase->shipping_address ?? '--'}}
        @endif
        @if(!empty($purchase->delivered_to))
          <br><strong>@lang('lang_v1.delivered_to'): </strong> {{$purchase->delivered_to}}
        @endif
        @if(!empty($purchase->shipping_custom_field_1))
          <br><strong>{{$custom_labels['shipping']['custom_field_1'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_1}}
        @endif
        @if(!empty($purchase->shipping_custom_field_2))
          <br><strong>{{$custom_labels['shipping']['custom_field_2'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_2}}
        @endif
        @if(!empty($purchase->shipping_custom_field_3))
          <br><strong>{{$custom_labels['shipping']['custom_field_3'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_3}}
        @endif
        @if(!empty($purchase->shipping_custom_field_4))
          <br><strong>{{$custom_labels['shipping']['custom_field_4'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_4}}
        @endif
        @if(!empty($purchase->shipping_custom_field_5))
          <br><strong>{{$custom_labels['shipping']['custom_field_5'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_5}}
        @endif
        @php
          $medias = $purchase->media->where('model_media_type', 'shipping_document')->all();
        @endphp
        @if(count($medias))
          @include('sell.partials.media_table', ['medias' => $medias])
        @endif
      @endif
    </div>
  </div>

  <br>
  <div class="row">
    <div class="col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table bg-gray">
          <thead>
            <tr class="bg-green">
              <th>#</th>
              <th>@lang('product.product_name')</th>
              <th>@lang('product.sku')</th>
              @if($purchase->type == 'purchase_order')
                <th class="text-right">@lang( 'lang_v1.quantity_remaining' )</th>
              @endif
              <th class="text-right">@if($purchase->type == 'purchase_order') @lang('lang_v1.order_quantity') @else @lang('purchase.purchase_quantity') @endif</th>
              <th class="text-right">@lang( 'lang_v1.unit_cost_before_discount' )</th>
              <th class="text-right">@lang( 'lang_v1.discount_percent' )</th>
              <th class="no-print text-right">@lang('purchase.unit_cost_before_tax')</th>
              <th class="no-print text-right">@lang('purchase.subtotal_before_tax')</th>
              <th class="text-right">@lang('sale.tax')</th>
              <th class="text-right">@lang('purchase.unit_cost_after_tax')</th>
              @if($purchase->type != 'purchase_order')
              <th class="text-right">@lang('purchase.unit_selling_price')</th>
              @if(session('business.enable_lot_number'))
                <th>@lang('lang_v1.lot_number')</th>
              @endif
              @if(session('business.enable_product_expiry'))
                <th>@lang('product.mfg_date')</th>
                <th>@lang('product.exp_date')</th>
              @endif
              @endif
              <th class="text-right">@lang('sale.subtotal')</th>
            </tr>
          </thead>
          @php 
            $total_before_tax = 0.00;
          @endphp
          @foreach($purchase->purchase_lines as $purchase_line)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                {{ $purchase_line->product->name }}
                 @if( $purchase_line->product->type == 'variable')
                  - {{ $purchase_line->variations->product_variation->name}}
                  - {{ $purchase_line->variations->name}}
                 @endif
              </td>
              <td>
                 @if( $purchase_line->product->type == 'variable')
                  {{ $purchase_line->variations->sub_sku}}
                  @else
                  {{ $purchase_line->product->sku }}
                 @endif
              </td>
              @if($purchase->type == 'purchase_order')
              <td>
                <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line->quantity - $purchase_line->po_quantity_purchased }}</span> @if(!empty($purchase_line->sub_unit)) {{$purchase_line->sub_unit->short_name}} @else {{$purchase_line->product->unit->short_name}} @endif
              </td>
              @endif
              <td><span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line->quantity }}</span> @if(!empty($purchase_line->sub_unit)) {{$purchase_line->sub_unit->short_name}} @else {{$purchase_line->product->unit->short_name}} @endif</td>
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->pp_without_discount}}</span></td>
              <td class="text-right"><span class="display_currency">{{ $purchase_line->discount_percent}}</span> %</td>
              <td class="no-print text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price }}</span></td>
              <td class="no-print text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->quantity * $purchase_line->purchase_price }}</span></td>
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->item_tax }} </span> <br/><small>@if(!empty($taxes[$purchase_line->tax_id])) ( {{ $taxes[$purchase_line->tax_id]}} ) </small>@endif</td>
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax }}</span></td>
              @if($purchase->type != 'purchase_order')
              @php
                $sp = $purchase_line->variations->default_sell_price;
                if(!empty($purchase_line->sub_unit->base_unit_multiplier)) {
                  $sp = $sp * $purchase_line->sub_unit->base_unit_multiplier;
                }
              @endphp
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{$sp}}</span></td>

              @if(session('business.enable_lot_number'))
                <td>{{$purchase_line->lot_number}}</td>
              @endif

              @if(session('business.enable_product_expiry'))
              <td>
                @if(!empty($purchase_line->mfg_date))
                    {{ @format_date($purchase_line->mfg_date) }}
                @endif
              </td>
              <td>
                @if(!empty($purchase_line->exp_date))
                    {{ @format_date($purchase_line->exp_date) }}
                @endif
              </td>
              @endif
              @endif
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax * $purchase_line->quantity }}</span></td>
            </tr>
            @php 
              $total_before_tax += ($purchase_line->quantity * $purchase_line->purchase_price);
            @endphp
          @endforeach
        </table>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    @if(!empty($purchase->type == 'purchase'))
    <div class="col-sm-12 col-xs-12">
      <h4>{{ __('sale.payment_info') }}:</h4>
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table">
          <tr class="bg-green">
            <th>#</th>
            <th>{{ __('messages.date') }}</th>
            <th>{{ __('purchase.ref_no') }}</th>
            <th>{{ __('sale.amount') }}</th>
            <th>{{ __('sale.payment_mode') }}</th>
            <th>{{ __('sale.payment_note') }}</th>
          </tr>
          @php
            $total_paid = 0;
          @endphp
          @forelse($purchase->payment_lines as $payment_line)
            @php
              $total_paid += $payment_line->amount;
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ @format_date($payment_line->paid_on) }}</td>
              <td>{{ $payment_line->payment_ref_no }}</td>
              <td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
              <td>{{ $payment_methods[$payment_line->method] ?? '' }}</td>
              <td>@if($payment_line->note) 
                {{ ucfirst($payment_line->note) }}
                @else
                --
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center">
                @lang('purchase.no_payments')
              </td>
            </tr>
          @endforelse
        </table>
      </div>
    </div>
    @endif
    <div class="col-md-6 col-sm-12 col-xs-12 @if($purchase->type == 'purchase_order') col-md-offset-6 @endif">
      <div class="table-responsive">
        <table class="table">
          <!-- <tr class="hide">
            <th>@lang('purchase.total_before_tax'): </th>
            <td></td>
            <td><span class="display_currency pull-right">{{ $total_before_tax }}</span></td>
          </tr> -->
          <tr>
            <th>@lang('purchase.net_total_amount'): </th>
            <td></td>
            <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $total_before_tax }}</span></td>
          </tr>
          <tr>
            <th>@lang('purchase.discount'):</th>
            <td>
              <b>(-)</b>
              @if($purchase->discount_type == 'percentage')
                ({{$purchase->discount_amount}} %)
              @endif
            </td>
            <td>
              <span class="display_currency pull-right" data-currency_symbol="true">
                @if($purchase->discount_type == 'percentage')
                  {{$purchase->discount_amount * $total_before_tax / 100}}
                @else
                  {{$purchase->discount_amount}}
                @endif                  
              </span>
            </td>
          </tr>
          <tr>
            <th>@lang('purchase.purchase_tax'):</th>
            <td><b>(+)</b></td>
            <td class="text-right">
                @if(!empty($purchase_taxes))
                  @foreach($purchase_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
          </tr>
          @if( !empty( $purchase->shipping_charges ) )
            <tr>
              <th>@lang('purchase.additional_shipping_charges'):</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" >{{ $purchase->shipping_charges }}</span></td>
            </tr>
          @endif
          @if( !empty( $purchase->additional_expense_value_1 )  && !empty( $purchase->additional_expense_key_1 ))
            <tr>
              <th>{{ $purchase->additional_expense_key_1 }}:</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" >{{ $purchase->additional_expense_value_1 }}</span></td>
            </tr>
          @endif
          @if( !empty( $purchase->additional_expense_value_2 )  && !empty( $purchase->additional_expense_key_2 ))
            <tr>
              <th>{{ $purchase->additional_expense_key_2 }}:</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" >{{ $purchase->additional_expense_value_2 }}</span></td>
            </tr>
          @endif
          @if( !empty( $purchase->additional_expense_value_3 )  && !empty( $purchase->additional_expense_key_3 ))
            <tr>
              <th>{{ $purchase->additional_expense_key_3 }}:</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" >{{ $purchase->additional_expense_value_3 }}</span></td>
            </tr>
          @endif
          @if( !empty( $purchase->additional_expense_value_4 ) && !empty( $purchase->additional_expense_key_4 ))
            <tr>
              <th>{{ $purchase->additional_expense_key_4 }}:</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" >{{ $purchase->additional_expense_value_4 }}</span></td>
            </tr>
          @endif
          <tr>
            <th>@lang('purchase.purchase_total'):</th>
            <td></td>
            <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $purchase->final_total }}</span></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <strong>@lang('purchase.shipping_details'):</strong><br>
      <p class="well well-sm no-shadow bg-gray">
        {{ $purchase->shipping_details ?? '' }}

        @if(!empty($purchase->shipping_custom_field_1))
          <br><strong>{{$custom_labels['purchase_shipping']['custom_field_1'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_1}}
        @endif
        @if(!empty($purchase->shipping_custom_field_2))
          <br><strong>{{$custom_labels['purchase_shipping']['custom_field_2'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_2}}
        @endif
        @if(!empty($purchase->shipping_custom_field_3))
          <br><strong>{{$custom_labels['purchase_shipping']['custom_field_3'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_3}}
        @endif
        @if(!empty($purchase->shipping_custom_field_4))
          <br><strong>{{$custom_labels['purchase_shipping']['custom_field_4'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_4}}
        @endif
        @if(!empty($purchase->shipping_custom_field_5))
          <br><strong>{{$custom_labels['purchase_shipping']['custom_field_5'] ?? ''}}: </strong> {{$purchase->shipping_custom_field_5}}
        @endif
      </p>
    </div>
    <div class="col-sm-6">
      <strong>@lang('purchase.additional_notes'):</strong><br>
      <p class="well well-sm no-shadow bg-gray">
        @if($purchase->additional_notes)
          {{ $purchase->additional_notes }}
        @else
          --
        @endif
      </p>
    </div>
  </div>
  @if(!empty($activities))
  <div class="row">
    <div class="col-md-12">
          <strong>{{ __('lang_v1.activities') }}:</strong><br>
          @includeIf('activity_log.activities', ['activity_type' => 'purchase'])
      </div>
  </div>
  @endif

  {{-- Barcode --}}
  <div class="row print_section">
    <div class="col-xs-12">
      <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($purchase->ref_no, 'C128', 2,30,array(39, 48, 54), true)}}">
    </div>
  </div>
</div>