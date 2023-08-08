@lang('business.business'):
<address>
    <strong>{{ $transaction->business->name }}</strong>
    {{ $transaction->location->name ?? '' }}
    @if(!empty($transaction->location->landmark))
        <br>{{$transaction->location->landmark}}
    @endif
    @if(!empty($transaction->location->city) || !empty($transaction->location->state) || !empty($transaction->location->country))
        <br>{{implode(',', array_filter([$transaction->location->city, $transaction->location->state, $transaction->location->country]))}}
    @endif
  
    @if(!empty($transaction->business->tax_number_1))
        <br>{{$transaction->business->tax_label_1}}: {{$transaction->business->tax_number_1}}
    @endif

    @if(!empty($transaction->business->tax_number_2))
        <br>{{$transaction->business->tax_label_2}}: {{$transaction->business->tax_number_2}}
    @endif

    @if(!empty($transaction->location->mobile))
        <br>@lang('contact.mobile'): {{$transaction->location->mobile}}
    @endif
    @if(!empty($transaction->location->email))
        <br>@lang('business.email'): {{$transaction->location->email}}
    @endif
</address>