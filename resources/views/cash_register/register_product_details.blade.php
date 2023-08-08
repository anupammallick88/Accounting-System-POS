<div class="row">
  <div class="col-md-12">
    <hr>
    <h3>@lang('lang_v1.product_sold_details_register')</h3>
    <table class="table table-condensed">
      <tr>
        <th>#</th>
        <th>@lang('brand.brands')</th>
        <th>@lang('sale.qty')</th>
        <th>@lang('sale.total_amount')</th>
      </tr>
      @php
        $total_amount = 0;
        $total_quantity = 0;
      @endphp
      @foreach($details['product_details'] as $detail)
        <tr>
          <td>
            {{$loop->iteration}}.
          </td>
          <td>
            {{$detail->brand_name}}
          </td>
          <td>
            {{@format_quantity($detail->total_quantity)}}
            @php
              $total_quantity += $detail->total_quantity;
            @endphp
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">
              {{$detail->total_amount}}
            </span>
            @php
              $total_amount += $detail->total_amount;
            @endphp
          </td>
        </tr>
      @endforeach

      
      @php
        $total_amount += ($details['transaction_details']->total_tax - $details['transaction_details']->total_discount);

        $total_amount += $details['transaction_details']->total_shipping_charges;
      @endphp

      <!-- Final details -->
      <tr class="success">
        <th>#</th>
        <th></th>
        <th>{{$total_quantity}}</th>
        <th>

          @if($details['transaction_details']->total_tax != 0)
            @lang('sale.order_tax'): (+)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_tax}}
            </span>
            <br/>
          @endif

          @if($details['transaction_details']->total_discount != 0)
            @lang('sale.discount'): (-)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_discount}}
            </span>
            <br/>
          @endif
          @if($details['transaction_details']->total_shipping_charges != 0)
            @lang('lang_v1.total_shipping_charges'): (+)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_shipping_charges}}
            </span>
            <br/>
          @endif

          @lang('lang_v1.grand_total'):
          <span class="display_currency" data-currency_symbol="true">
            {{$total_amount}}
          </span>
        </th>
      </tr>

    </table>
  </div>
</div>

@if($details['types_of_service_details'])
  <div class="row">
    <div class="col-md-12">
      <hr>
      <h3>@lang('lang_v1.types_of_service_details')</h3>
      <table class="table">
        <tr>
          <th>#</th>
          <th>@lang('lang_v1.types_of_service')</th>
          <th>@lang('sale.total_amount')</th>
        </tr>
        @php
          $total_sales = 0;
        @endphp
        @foreach($details['types_of_service_details'] as $detail)
          <tr>
            <td>
              {{$loop->iteration}}
            </td>
            <td>
              {{$detail->types_of_service_name ?? "--"}}
            </td>
            <td>
              <span class="display_currency" data-currency_symbol="true">
                {{$detail->total_sales}}
              </span>
              @php
                $total_sales += $detail->total_sales;
              @endphp
            </td>
          </tr>
          @php
            $total_sales += $detail->total_sales;
          @endphp
        @endforeach
        <!-- Final details -->
        <tr class="success">
          <th>#</th>
          <th></th>
          <th>
            @lang('lang_v1.grand_total'):
            <span class="display_currency" data-currency_symbol="true">
              {{$total_amount}}
            </span>
          </th>
        </tr>

      </table>
    </div>
  </div>
@endif