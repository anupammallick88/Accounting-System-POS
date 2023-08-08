<div class="table-responsive">
<table class="table table-bordered table-striped" id="sr_payments_with_commission_table" style="width: 100%;">
        <thead>
            <tr>
                <th>@lang('purchase.ref_no')</th>
                <th>@lang('lang_v1.paid_on')</th>
                <th>@lang('sale.amount')</th>
                <th>@lang('contact.customer')</th>
                <th>@lang('lang_v1.payment_method')</th>
                <th>@lang('sale.sale')</th>
                <th>@lang('messages.action')</th>
            </tr>
        </thead>
        <tfoot>
            <tr class="bg-gray font-17 footer-total text-center">
                <td colspan="2"><strong>@lang('sale.total'):</strong></td>
                <td><span class="display_currency" id="footer_total_amount" data-currency_symbol ="true"></span></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
</div>