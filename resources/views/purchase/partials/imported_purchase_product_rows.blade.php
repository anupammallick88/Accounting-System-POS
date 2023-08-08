@foreach($formatted_data as $data)
	@include('purchase.partials.purchase_entry_row', [
		'variations' => [$data['variation']],
		'product' => $data['product'],
		'row_count' => $row_count,
		'variation_id' => $data['variation']->id,
		'taxes' => $taxes,
		'currency_details' => $currency_details,
		'hide_tax' => $hide_tax,
		'sub_units' => $data['sub_units'],
		'imported_data' =>  $data
	])
	@php
		$row_count++;
	@endphp
@endforeach