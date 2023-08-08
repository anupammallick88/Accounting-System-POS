@extends('layouts.app')
@section('title', __( 'report.tax_report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'report.tax_report' )
        <small>@lang( 'report.tax_report_msg' )</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('tax_report_location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('tax_report_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('tax_report_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('tax_report_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'tax_report_date_range', 'readonly']); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    {{--<div class="row">
        <div class="col-md-4 col-sm-12">
            @component('components.widget')
                @slot('title')
                    {{ __('report.input_tax') }} @show_tooltip(__('tooltip.input_tax'))
                @endslot
                <div class="input_tax">
                    <i class="fas fa-sync fa-spin fa-fw"></i>
                </div>
            @endcomponent
        </div>

        <div class="col-md-4 col-sm-12">
            @component('components.widget')
                @slot('title')
                    {{ __('report.output_tax') }} @show_tooltip(__('tooltip.output_tax'))
                @endslot
                <div class="output_tax">
                    <i class="fas fa-sync fa-spin fa-fw"></i>
                </div>
            @endcomponent
        </div>

        <div class="col-md-4 col-sm-12">
            @component('components.widget')
                @slot('title')
                    {{ __('lang_v1.expense_tax') }} @show_tooltip(__('lang_v1.expense_tax_tooltip'))
                @endslot
                <div class="expense_tax">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </div>
            @endcomponent
        </div>
    </div>--}}

    <div class="row">
        <div class="col-xs-12">
            @component('components.widget')
                @slot('title')
                    {{ __('lang_v1.tax_overall') }} @show_tooltip(__('tooltip.tax_overall'))
                @endslot
                <h3 class="text-muted">
                    {{ __('lang_v1.output_tax_minus_input_tax') }}: 
                    <span class="tax_diff">
                        <i class="fas fa-sync fa-spin fa-fw"></i>
                    </span>
                </h3>
            @endcomponent
        </div>
    </div>
    <div class="row no-print">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" onclick="window.print();"
            ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
           <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#input_tax_tab" data-toggle="tab" aria-expanded="true"><i class="fa fas fa-arrow-circle-down" aria-hidden="true"></i> @lang('report.input_tax')</a>
                    </li>

                    <li>
                        <a href="#output_tax_tab" data-toggle="tab" aria-expanded="true"><i class="fa fas fa-arrow-circle-up" aria-hidden="true"></i> @lang('report.output_tax')</a>
                    </li>

                    <li>
                        <a href="#expense_tax_tab" data-toggle="tab" aria-expanded="true"><i class="fa fas fa-minus-circle" aria-hidden="true"></i> @lang('lang_v1.expense_tax')</a>
                    </li>
                    @if(!empty($tax_report_tabs))
                        @foreach($tax_report_tabs as $key => $tabs)
                            @foreach ($tabs as $index => $value)
                                @if(!empty($value['tab_menu_path']))
                                    @php
                                        $tab_data = !empty($value['tab_data']) ? $value['tab_data'] : [];
                                    @endphp
                                    @include($value['tab_menu_path'], $tab_data)
                                @endif
                            @endforeach
                        @endforeach
                    @endif
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="input_tax_tab">
                        <table class="table table-bordered table-striped" id="input_tax_table">
                            <thead>
                                <tr>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('purchase.ref_no')</th>
                                    <th>@lang('purchase.supplier')</th>
                                    <th>@lang('contact.tax_no')</th>
                                    <th>@lang('sale.total_amount')</th>
                                    <th>@lang('lang_v1.payment_method')</th>
                                    <th>@lang('receipt.discount')</th>
                                    @foreach($taxes as $tax)
                                        <th>
                                            {{$tax['name']}}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 text-center footer-total">
                                    <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                                    <td><span class="display_currency" id="sell_total" data-currency_symbol ="true"></span></td>
                                    <td class="input_payment_method_count"></td>
                                    <td>&nbsp;</td>
                                    @foreach($taxes as $tax)
                                        <td>
                                            <span class="display_currency" id="total_input_{{$tax['id']}}" data-currency_symbol ="true"></span>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="tab-pane" id="output_tax_tab">
                        <table class="table table-bordered table-striped" id="output_tax_table" width="100%">
                            <thead>
                                <tr>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('sale.invoice_no')</th>
                                    <th>@lang('contact.customer')</th>
                                    <th>@lang('contact.tax_no')</th>
                                    <th>@lang('sale.total_amount')</th>
                                    <th>@lang('lang_v1.payment_method')</th>
                                    <th>@lang('receipt.discount')</th>
                                    @foreach($taxes as $tax)
                                        <th>
                                            {{$tax['name']}}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 text-center footer-total">
                                    <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                                    <td><span class="display_currency" id="purchase_total" data-currency_symbol ="true"></span></td>
                                    <td class="output_payment_method_count"></td>
                                    <td>&nbsp;</td>
                                    @foreach($taxes as $tax)
                                        <td>
                                            <span class="display_currency" id="total_output_{{$tax['id']}}" data-currency_symbol ="true"></span>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="tab-pane" id="expense_tax_tab">
                        <table class="table table-bordered table-striped" id="expense_tax_table" width="100%">
                            <thead>
                                <tr>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('purchase.ref_no')</th>
                                    <th>@lang('contact.tax_no')</th>
                                    <th>@lang('sale.total_amount')</th>
                                    <th>@lang('lang_v1.payment_method')</th>
                                    @foreach($taxes as $tax)
                                        <th>
                                            {{$tax['name']}}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 text-center footer-total">
                                    <td colspan="3"><strong>@lang('sale.total'):</strong></td>
                                    <td>
                                        <span class="display_currency" id="expense_total" data-currency_symbol ="true"></span>
                                    </td> 
                                    <td class="expense_payment_method_count"></td>
                                    @foreach($taxes as $tax)
                                        <td>
                                            <span class="display_currency" id="total_expense_{{$tax['id']}}" data-currency_symbol ="true"></span>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if(!empty($tax_report_tabs))
                        @foreach($tax_report_tabs as $key => $tabs)
                            @foreach ($tabs as $index => $value)
                                @if(!empty($value['tab_content_path']))
                                    @php
                                        $tab_data = !empty($value['tab_data']) ? $value['tab_data'] : [];
                                    @endphp
                                    @include($value['tab_content_path'], $tab_data)
                                @endif
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('#tax_report_date_range').daterangepicker(
            dateRangeSettings, 
            function(start, end) {
                $('#tax_report_date_range').val(
                    start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                );
            }
        );

        input_tax_table = $('#input_tax_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/reports/tax-details',
                data: function(d) {
                    d.type = 'purchase';
                    d.location_id = $('#tax_report_location_id').val();
                    var start = $('input#tax_report_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    var end = $('input#tax_report_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
            },
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'contact_name', name: 'c.name' },
                { data: 'tax_number', name: 'c.tax_number' },
                { data: 'total_before_tax', name: 'total_before_tax' },
                { data: 'payment_methods', orderable: false, "searchable": false},
                { data: 'discount_amount', name: 'discount_amount' },
                @foreach($taxes as $tax)
                { data: "tax_{{$tax['id']}}", searchable: false, orderable: false },
                @endforeach
            ],
            "footerCallback": function ( row, data, start, end, display ) {
                $('.input_payment_method_count').html(__count_status(data, 'payment_methods'));
            },
            fnDrawCallback: function(oSettings) {
                $('#sell_total').text(
                    sum_table_col($('#input_tax_table'), 'total_before_tax')
                );
                @foreach($taxes as $tax)
                    $("#total_input_{{$tax['id']}}").text(
                        sum_table_col($('#input_tax_table'), "tax_{{$tax['id']}}")
                    );
                @endforeach

                __currency_convert_recursively($('#input_tax_table'));
            },
        });
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') == '#output_tax_tab') {
                if (typeof (output_tax_datatable) == 'undefined') {
                    output_tax_datatable = $('#output_tax_table').DataTable({
                        processing: true,
                        serverSide: true,
                        aaSorting: [[0, 'desc']],
                        ajax: {
                            url: '/reports/tax-details',
                            data: function(d) {
                                d.type = 'sell';
                                d.location_id = $('#tax_report_location_id').val();
                                var start = $('input#tax_report_date_range')
                                    .data('daterangepicker')
                                    .startDate.format('YYYY-MM-DD');
                                var end = $('input#tax_report_date_range')
                                    .data('daterangepicker')
                                    .endDate.format('YYYY-MM-DD');
                                d.start_date = start;
                                d.end_date = end;
                            }
                        },
                        columns: [
                            { data: 'transaction_date', name: 'transaction_date' },
                            { data: 'invoice_no', name: 'invoice_no' },
                            { data: 'contact_name', name: 'c.name' },
                            { data: 'tax_number', name: 'c.tax_number' },
                            { data: 'total_before_tax', name: 'total_before_tax' },
                            { data: 'payment_methods', orderable: false, "searchable": false},
                            { data: 'discount_amount', name: 'discount_amount' },
                            @foreach($taxes as $tax)
                            { data: "tax_{{$tax['id']}}", searchable: false, orderable: false },
                            @endforeach
                        ],
                        "footerCallback": function ( row, data, start, end, display ) {
                            $('.output_payment_method_count').html(__count_status(data, 'payment_methods'));
                        },
                        fnDrawCallback: function(oSettings) {
                            $('#purchase_total').text(
                                sum_table_col($('#output_tax_table'), 'total_before_tax')
                            );
                            @foreach($taxes as $tax)
                                $("#total_output_{{$tax['id']}}").text(
                                    sum_table_col($('#output_tax_table'), "tax_{{$tax['id']}}")
                                );
                            @endforeach
                            __currency_convert_recursively($('#output_tax_table'));
                        },
                    });
                }
            } else if ($(e.target).attr('href') == '#expense_tax_tab') {
                if (typeof (expense_tax_datatable) == 'undefined') {
                    expense_tax_datatable = $('#expense_tax_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '/reports/tax-details',
                            data: function(d) {
                                d.type = 'expense';
                                d.location_id = $('#tax_report_location_id').val();
                                var start = $('input#tax_report_date_range')
                                    .data('daterangepicker')
                                    .startDate.format('YYYY-MM-DD');
                                var end = $('input#tax_report_date_range')
                                    .data('daterangepicker')
                                    .endDate.format('YYYY-MM-DD');
                                d.start_date = start;
                                d.end_date = end;
                            }
                        },
                        columns: [
                            { data: 'transaction_date', name: 'transaction_date' },
                            { data: 'ref_no', name: 'ref_no' },
                            { data: 'tax_number', name: 'c.tax_number' },
                            { data: 'total_before_tax', name: 'total_before_tax' },
                            { data: 'payment_methods', orderable: false, "searchable": false},
                            @foreach($taxes as $tax)
                            { data: "tax_{{$tax['id']}}", searchable: false, orderable: false },
                            @endforeach
                        ],
                        "footerCallback": function ( row, data, start, end, display ) {
                            $('.expense_payment_method_count').html(__count_status(data, 'payment_methods'));
                        },
                        fnDrawCallback: function(oSettings) {
                            $('#expense_total').text(
                                sum_table_col($('#expense_tax_table'), 'total_before_tax')
                            );
                            @foreach($taxes as $tax)
                                $("#total_expense_{{$tax['id']}}").text(
                                    sum_table_col($('#expense_tax_table'), "tax_{{$tax['id']}}")
                                );
                            @endforeach
                            __currency_convert_recursively($('#expense_tax_table'));
                        },
                    });
                }
            }
        });
        
        $('#tax_report_date_range, #tax_report_location_id').change( function(){
            if ($("#input_tax_tab").hasClass('active')) {
                input_tax_table.ajax.reload();
            }
            if ($("#output_tax_tab").hasClass('active')) {
                output_tax_datatable.ajax.reload();
            }
            if ($("#expense_tax_tab").hasClass('active')) {
                expense_tax_datatable.ajax.reload();
            }
        });
    });
</script>
@if(!empty($tax_report_tabs))
    @foreach($tax_report_tabs as $key => $tabs)
        @foreach ($tabs as $index => $value)
            @if(!empty($value['module_js_path']))
                @include($value['module_js_path'])
            @endif
        @endforeach
    @endforeach
@endif
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection