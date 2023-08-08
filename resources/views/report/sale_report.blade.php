@extends('layouts.app')
@section('title', 'Report 607 (' . __('business.sale') . ')')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>Report 607 (@lang('business.sale'))
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="table-responsive">
    <table class="table table-bordered table-striped ajax_view" id="sale_report_table">
        <thead>
            <tr>
                <th>@lang('lang_v1.contact_id')</th>
                <th>@lang('sale.customer_name')</th>
                <th>@lang('sale.invoice_no')</th>
                <th>@lang('messages.date')</th>
                <th>@lang('sale.total') (@lang('product.exc_of_tax'))</th>
                <th>@lang('sale.discount')</th>
                <th>@lang('sale.tax')</th>
                <th>@lang('sale.total') (@lang('product.inc_of_tax'))</th>
                <th>@lang('lang_v1.payment_method')</th>
            </tr>
        </thead>
    </table>
</div>
    @endcomponent

</section>

<section id="receipt_section" class="print_section"></section>

<!-- /.content -->
@stop
@section('javascript')

<script type="text/javascript">
    $(document).ready(function() {
        $('#sell_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                sale_report_table.ajax.reload();
            }
        );
        $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#sell_list_filter_date_range').val('');
            sale_report_table.ajax.reload();
        });

        sale_report_table = $('#sale_report_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[1, 'desc']],
            "ajax": {
                "url": "/sells",
                "data": function ( d ) {
                    if($('#sell_list_filter_date_range').val()) {
                        var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }
                    d.location_id = $('#sell_list_filter_location_id').val();
                    d.customer_id = $('#sell_list_filter_customer_id').val();
                    d.payment_status = $('#sell_list_filter_payment_status').val();
                    d = __datatable_ajax_callback(d);
                }
            },
            columns: [
                { data: 'contact_id', name: 'contacts.contact_id' },
                { data: 'name', name: 'contacts.name' },
                { data: 'invoice_no_text', name: 'transactions.invoice_no' },
                { data: 'sale_date', name: 'transactions.transaction_date' },
                { data: 'total_before_tax', name: 'total_before_tax' },
                { data: 'discount_amount', name: 'discount_amount' },
                { data: 'tax_amount', name: 'tax_amount' },
                { data: 'final_total', name: 'final_total' },
                { data: 'payment_methods', name: 'payment_methods' },
            ],
            "fnDrawCallback": function (oSettings) {
                __currency_convert_recursively($('#sale_report_table'));
            }
        });

        $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs',  function() {
            sale_report_table.ajax.reload();
        });
    });
</script>
	
@endsection