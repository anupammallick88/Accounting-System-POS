@extends('layouts.app')
@section('title', __('lang_v1.product_sell_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>{{ __('lang_v1.product_sell_report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action('ReportController@getStockReport'), 'method' => 'get', 'id' => 'product_sell_report_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                    {!! Form::label('search_product', __('lang_v1.search_product') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <input type="hidden" value="" id="variation_id">
                            {!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'), 'autofocus']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('psr_customer_group_id', __( 'lang_v1.customer_group_name' ) . ':') !!}
                        {!! Form::select('psr_customer_group_id', $customer_group, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'psr_customer_group_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':') !!}
                        {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'psr_filter_category_id', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                        {!! Form::select('brand_id', $brands, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'psr_filter_brand_id', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('product_sr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'product_sr_date_filter', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    {!! Form::label('product_sr_start_time', __('lang_v1.time_range') . ':') !!}
                    @php
                        $startDay = Carbon::now()->startOfDay();
                        $endDay   = $startDay->copy()->endOfDay();
                    @endphp
                    <div class="form-group">
                        {!! Form::text('start_time', @format_time($startDay), ['style' => __('lang_v1.select_a_date_range'), 'class' => 'form-control width-50 f-left', 'id' => 'product_sr_start_time']); !!}
                        {!! Form::text('end_time', @format_time($endDay), ['class' => 'form-control width-50 f-left', 'id' => 'product_sr_end_time']); !!}
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#psr_detailed_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-list" aria-hidden="true"></i> @lang('lang_v1.detailed')</a>
                    </li>
                    <li>
                        <a href="#psr_detailed_with_purchase_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-list" aria-hidden="true"></i> @lang('lang_v1.detailed_with_purchase')</a>
                    </li>
                    <li>
                        <a href="#psr_grouped_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-bars" aria-hidden="true"></i> @lang('lang_v1.grouped')</a>
                    </li>
                    <li>
                        <a href="#psr_by_cat_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-bars" aria-hidden="true"></i> @lang('lang_v1.by_category')</a>
                    </li>
                    <li>
                        <a href="#psr_by_brand_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-bars" aria-hidden="true"></i> @lang('lang_v1.by_brand')</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="psr_detailed_tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" 
                            id="product_sell_report_table">
                                <thead>
                                    <tr>
                                        <th>@lang('sale.product')</th>
                                        <th>@lang('product.sku')</th>
                                        <th>@lang('sale.customer_name')</th>
                                        <th>@lang('lang_v1.contact_id')</th>
                                        <th>@lang('sale.invoice_no')</th>
                                        <th>@lang('messages.date')</th>
                                        <th>@lang('sale.qty')</th>
                                        <th>@lang('sale.unit_price')</th>
                                        <th>@lang('sale.discount')</th>
                                        <th>@lang('sale.tax')</th>
                                        <th>@lang('sale.price_inc_tax')</th>
                                        <th>@lang('sale.total')</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr class="bg-gray font-17 footer-total text-center">
                                        <td colspan="6"><strong>@lang('sale.total'):</strong></td>
                                        <td id="footer_total_sold"></td>
                                        <td></td>
                                        <td></td>
                                        <td id="footer_tax"></td>
                                        <td></td>
                                        <td><span class="display_currency" id="footer_subtotal" data-currency_symbol ="true"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="psr_detailed_with_purchase_tab">
                        <div class="table-responsive">
                            @if(session('business.enable_lot_number'))
                                <input type="hidden" id="lot_enabled">
                            @endif
                            <table class="table table-bordered table-striped" 
                            id="product_sell_report_with_purchase_table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>@lang('sale.product')</th>
                                        <th>@lang('product.sku')</th>
                                        <th>@lang('sale.customer_name')</th>
                                        <th>@lang('sale.invoice_no')</th>
                                        <th>@lang('messages.date')</th>
                                        <th>@lang('lang_v1.purchase_ref_no')</th>
                                        <th>@lang('lang_v1.lot_number')</th>
                                        <th>@lang('lang_v1.supplier_name')</th>
                                        <th>@lang('sale.qty')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane" id="psr_grouped_tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" 
                            id="product_sell_grouped_report_table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>@lang('sale.product')</th>
                                        <th>@lang('product.sku')</th>
                                        <th>@lang('messages.date')</th>
                                        <th>@lang('report.current_stock')</th>
                                        <th>@lang('report.total_unit_sold')</th>
                                        <th>@lang('sale.total')</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr class="bg-gray font-17 footer-total text-center">
                                        <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                                        <td id="footer_total_grouped_sold"></td>
                                        <td><span class="display_currency" id="footer_grouped_subtotal" data-currency_symbol ="true"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @include('report.partials.product_sell_report_by_category')

                    @include('report.partials.product_sell_report_by_brand')
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(
        '#product_sell_report_form #location_id, #product_sell_report_form #customer_id, #psr_filter_brand_id, #psr_filter_category_id, #psr_customer_group_id'
    ).change(function() {
        $('.nav-tabs li.active').find('a[data-toggle="tab"]').trigger('shown.bs.tab');
    });
        $(document).ready( function() {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr('href');
                if ( target == '#psr_by_cat_tab') {
                    if(typeof product_sell_report_by_category_datatable == 'undefined') {
                        product_sell_report_by_category_datatable = $('table#product_sell_report_by_category').DataTable({
                                processing: true,
                                serverSide: true,
                                ajax: {
                                    url: '/reports/product-sell-grouped-by',
                                    data: function(d) {
                                        var start = '';
                                        var end = '';
                                        var start_time = $('#product_sr_start_time').val();
                                        var end_time = $('#product_sr_end_time').val();
                                        if ($('#product_sr_date_filter').val()) {
                                            start = $('input#product_sr_date_filter')
                                                .data('daterangepicker')
                                                .startDate.format('YYYY-MM-DD');
                                            end = $('input#product_sr_date_filter')
                                                .data('daterangepicker')
                                                .endDate.format('YYYY-MM-DD');

                                            start = moment(start + " " + start_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                                            end = moment(end + " " + end_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                                        }
                                        d.start_date = start;
                                        d.end_date = end;
                                        d.group_by = 'category';
                                        d.category_id = $('select#psr_filter_category_id').val();
                                        d.brand_id = $('select#psr_filter_brand_id').val();
                                        d.customer_id = $('select#customer_id').val();
                                        d.location_id = $('select#location_id').val();
                                        d.customer_group_id = $('#psr_customer_group_id').val();
                                    },
                                },
                                columns: [
                                    { data: 'category_name', name: 'cat.name' },
                                    { data: 'current_stock', name: 'current_stock', searchable: false, orderable: false },
                                    { data: 'total_qty_sold', name: 'total_qty_sold', searchable: false },
                                    { data: 'subtotal', name: 'subtotal', searchable: false },
                                ],
                                fnDrawCallback: function(oSettings) {
                                    $('#footer_psr_by_cat_total_sell').text(
                                        sum_table_col($('#product_sell_report_by_category'), 'row_subtotal')
                                    );
                                    $('#footer_psr_by_cat_total_sold').html(
                                        __sum_stock($('#product_sell_report_by_category'), 'sell_qty')
                                    );

                                    $('#footer_psr_by_cat_total_stock').html(
                                        __sum_stock($('#product_sell_report_by_category'), 'current_stock')
                                    );
                                    __currency_convert_recursively($('#product_sell_report_by_category'));
                                },
                            });
                        } else {
                            product_sell_report_by_category_datatable.ajax.reload();
                        }
                    } else if ( target == '#psr_by_brand_tab') {
                    if(typeof product_sell_report_by_brand_datatable == 'undefined') {
                        product_sell_report_by_brand_datatable = $('table#product_sell_report_by_brand').DataTable({
                                processing: true,
                                serverSide: true,
                                ajax: {
                                    url: '/reports/product-sell-grouped-by',
                                    data: function(d) {
                                        var start = '';
                                        var end = '';
                                        var start_time = $('#product_sr_start_time').val();
                                        var end_time = $('#product_sr_end_time').val();
                                        if ($('#product_sr_date_filter').val()) {
                                            start = $('input#product_sr_date_filter')
                                                .data('daterangepicker')
                                                .startDate.format('YYYY-MM-DD');
                                            end = $('input#product_sr_date_filter')
                                                .data('daterangepicker')
                                                .endDate.format('YYYY-MM-DD');

                                            start = moment(start + " " + start_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                                            end = moment(end + " " + end_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                                        }
                                        d.start_date = start;
                                        d.end_date = end;
                                        d.group_by = 'brand';
                                        d.category_id = $('select#psr_filter_category_id').val();
                                        d.brand_id = $('select#psr_filter_brand_id').val();
                                        d.customer_id = $('select#customer_id').val();
                                        d.location_id = $('select#location_id').val();
                                        d.customer_group_id = $('#psr_customer_group_id').val();
                                    },
                                },
                                columns: [
                                    { data: 'brand_name', name: 'b.name' },
                                    { data: 'current_stock', name: 'current_stock', searchable: false, orderable: false },
                                    { data: 'total_qty_sold', name: 'total_qty_sold', searchable: false },
                                    { data: 'subtotal', name: 'subtotal', searchable: false },
                                ],
                                fnDrawCallback: function(oSettings) {
                                    $('#footer_psr_by_brand_total_sell').text(
                                        sum_table_col($('#product_sell_report_by_brand'), 'row_subtotal')
                                    );
                                    $('#footer_psr_by_brand_total_sold').html(
                                        __sum_stock($('#product_sell_report_by_brand'), 'sell_qty')
                                    );

                                    $('#footer_psr_by_cat_total_stock').html(
                                        __sum_stock($('#product_sell_report_by_brand'), 'current_stock')
                                    );
                                    __currency_convert_recursively($('#product_sell_report_by_brand'));
                                },
                            });
                        } else {
                            product_sell_report_by_brand_datatable.ajax.reload();
                        }
                    }
                });
            });
    </script>
@endsection