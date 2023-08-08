@lang('purchase.supplier'):
<address>
    <strong>{{ $transaction->contact->supplier_business_name }}</strong>
    {{ $transaction->contact->name }}
    {!! $transaction->contact->contact_address !!}
    @if(!empty($transaction->contact->tax_number))
        <br>@lang('contact.tax_no'): {{$transaction->contact->tax_number}}
    @endif
    @if(!empty($transaction->contact->mobile))
        <br>@lang('contact.mobile'): {{$transaction->contact->mobile}}
    @endif
    @if(!empty($transaction->contact->email))
        <br>Email: {{$transaction->contact->email}}
    @endif
</address>