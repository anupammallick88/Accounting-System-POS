@extends('layouts.app')
@section('title', 'Report 606 (' . __('lang_v1.purchase') . ')')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>Report 606 (@lang('lang_v1.purchase'))
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('purchase_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_supplier_id',  __('purchase.supplier') . ':') !!}
                {!! Form::select('purchase_list_filter_supplier_id', $suppliers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_status',  __('purchase.purchase_status') . ':') !!}
                {!! Form::select('purchase_list_filter_status', $orderStatuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_payment_status',  __('purchase.payment_status') . ':') !!}
                {!! Form::select('purchase_list_filter_payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial'), 'overdue' => __('lang_v1.overdue')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('purchase_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="table-responsive">
    <table class="table table-bordered table-striped ajax_view" id="purchase_report_table">
        <thead>
            <tr>
                <th>@lang('lang_v1.contact_id')</th>
                <th>@lang('purchase.supplier')</th>
                <th>@lang('purchase.ref_no')</th>
                <th>@lang('purchase.purchase_date') (@lang('lang_v1.year_month'))</th>
                <th>@lang('purchase.purchase_date') (@lang('lang_v1.day'))</th>
                <th>@lang('lang_v1.payment_date') (@lang('lang_v1.year_month'))</th>
                <th>@lang('lang_v1.payment_date') (@lang('lang_v1.day'))</th>
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
        //Purchase report table
        purchase_report_table = $('#purchase_report_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/reports/purchase-report',
                data: function(d) {
                    if ($('#purchase_list_filter_location_id').length) {
                        d.location_id = $('#purchase_list_filter_location_id').val();
                    }
                    if ($('#purchase_list_filter_supplier_id').length) {
                        d.supplier_id = $('#purchase_list_filter_supplier_id').val();
                    }
                    if ($('#purchase_list_filter_payment_status').length) {
                        d.payment_status = $('#purchase_list_filter_payment_status').val();
                    }
                    if ($('#purchase_list_filter_status').length) {
                        d.status = $('#purchase_list_filter_status').val();
                    }

                    var start = '';
                    var end = '';
                    if ($('#purchase_list_filter_date_range').val()) {
                        start = $('input#purchase_list_filter_date_range')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                        end = $('input#purchase_list_filter_date_range')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                    }
                    d.start_date = start;
                    d.end_date = end;

                    d = __datatable_ajax_callback(d);
                },
            },
            columns: [
                { data: 'contact_id', name: 'contacts.contact_id' },
                { data: 'name', name: 'contacts.name' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'purchase_year_month', name: 'transaction_date' },
                { data: 'purchase_day', name: 'transaction_date' },
                { data: 'payment_year_month', searching: false },
                { data: 'payment_day', searching: false },
                { data: 'total_before_tax', name: 'total_before_tax' },
                { data: 'discount_amount', name: 'discount_amount' },
                { data: 'tax_amount', name: 'tax_amount' },
                { data: 'final_total', name: 'final_total' },
                { data: 'payment_method', name: 'payment_method' },
            ],
            fnDrawCallback: function(oSettings) {
                __currency_convert_recursively($('#purchase_report_table'));
            }
        });

        $(document).on(
            'change',
            '#purchase_list_filter_location_id, \
                        #purchase_list_filter_supplier_id, #purchase_list_filter_payment_status,\
                         #purchase_list_filter_status',
            function() {
                purchase_report_table.ajax.reload();
            }
        );
        $('#purchase_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
               purchase_report_table.ajax.reload();
            }
        );
        $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#purchase_list_filter_date_range').val('');
            purchase_report_table.ajax.reload();
        });
    });
</script>
	
@endsection