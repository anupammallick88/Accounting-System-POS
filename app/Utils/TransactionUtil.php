<?php

namespace App\Utils;

use App\AccountTransaction;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Currency;
use App\Events\TransactionPaymentAdded;
use App\Events\TransactionPaymentDeleted;
use App\Events\TransactionPaymentUpdated;
use App\Exceptions\PurchaseSellMismatch;
use App\Exceptions\AdvanceBalanceNotAvailable;
use App\InvoiceScheme;
use App\Product;
use App\PurchaseLine;
use App\Restaurant\ResTable;
use App\TaxRate;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\TransactionSellLinesPurchaseLines;
use App\Variation;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Utils\ModuleUtil;
use App\Utils\BusinessUtil;

class TransactionUtil extends Util
{
    /**
     * Add Sell transaction
     *
     * @param int $business_id
     * @param array $input
     * @param float $invoice_total
     * @param int $user_id
     *
     * @return boolean
     */
    public function createSellTransaction($business_id, $input, $invoice_total, $user_id, $uf_data = true)
    {
        $sale_type = !empty($input['type']) ? $input['type'] : 'sell';
        $invoice_scheme_id = !empty($input['invoice_scheme_id']) ? $input['invoice_scheme_id'] : null;
        $invoice_no = !empty($input['invoice_no']) ? $input['invoice_no'] : $this->getInvoiceNumber($business_id, $input['status'], $input['location_id'], $invoice_scheme_id, $sale_type);

        $final_total = $uf_data ? $this->num_uf($input['final_total']) : $input['final_total'];
        $transaction = Transaction::create([
            'business_id' => $business_id,
            'location_id' => $input['location_id'],
            'type' => $sale_type,
            'status' => $input['status'],
            'sub_status' => !empty($input['sub_status']) ? $input['sub_status'] : null,
            'contact_id' => $input['contact_id'],
            'customer_group_id' => !empty($input['customer_group_id']) ? $input['customer_group_id'] : null,
            'invoice_no' => $invoice_no,
            'ref_no' => '',
            'source' => !empty($input['source']) ? $input['source'] : null,
            'total_before_tax' => $invoice_total['total_before_tax'],
            'transaction_date' => $input['transaction_date'],
            'tax_id' => !empty($input['tax_rate_id']) ? $input['tax_rate_id'] : null,
            'discount_type' => !empty($input['discount_type']) ? $input['discount_type'] : null,
            'discount_amount' => $uf_data ? $this->num_uf($input['discount_amount']) : $input['discount_amount'],
            'tax_amount' => ($invoice_total['tax']),
            'final_total' => $final_total,
            'additional_notes' => !empty($input['sale_note']) ? $input['sale_note'] : null,
            'staff_note' => !empty($input['staff_note']) ? $input['staff_note'] : null,
            'created_by' => $user_id,
            'document' => !empty($input['document']) ? $input['document'] : null,
            'custom_field_1' => !empty($input['custom_field_1']) ? $input['custom_field_1'] : null,
            'custom_field_2' => !empty($input['custom_field_2']) ? $input['custom_field_2'] : null,
            'custom_field_3' => !empty($input['custom_field_3']) ? $input['custom_field_3'] : null,
            'custom_field_4' => !empty($input['custom_field_4']) ? $input['custom_field_4'] : null,
            'is_direct_sale' => !empty($input['is_direct_sale']) ? $input['is_direct_sale'] : 0,
            'commission_agent' => $input['commission_agent'] ?? null,
            'is_quotation' => isset($input['is_quotation']) ? $input['is_quotation'] : 0,
            'shipping_details' => isset($input['shipping_details']) ? $input['shipping_details'] : null,
            'shipping_address' => isset($input['shipping_address']) ? $input['shipping_address'] : null,
            'shipping_status' => isset($input['shipping_status']) ? $input['shipping_status'] : null,
            'delivered_to' => isset($input['delivered_to']) ? $input['delivered_to'] : null,
            'shipping_charges' => isset($input['shipping_charges']) ? $uf_data ? $this->num_uf($input['shipping_charges']) : $input['shipping_charges'] : 0,
            'shipping_custom_field_1' => !empty($input['shipping_custom_field_1']) ? $input['shipping_custom_field_1'] : null,
            'shipping_custom_field_2' => !empty($input['shipping_custom_field_2']) ? $input['shipping_custom_field_2'] : null,
            'shipping_custom_field_3' => !empty($input['shipping_custom_field_3']) ? $input['shipping_custom_field_3'] : null,
            'shipping_custom_field_4' => !empty($input['shipping_custom_field_4']) ? $input['shipping_custom_field_4'] : null,
            'shipping_custom_field_5' => !empty($input['shipping_custom_field_5']) ? $input['shipping_custom_field_5'] : null,
            'exchange_rate' => !empty($input['exchange_rate']) ?
                                $uf_data ? $this->num_uf($input['exchange_rate']) : $input['exchange_rate'] : 1,
            'selling_price_group_id' => isset($input['selling_price_group_id']) ? $input['selling_price_group_id'] : null,
            'pay_term_number' => isset($input['pay_term_number']) ? $input['pay_term_number'] : null,
            'pay_term_type' => isset($input['pay_term_type']) ? $input['pay_term_type'] : null,
            'is_suspend' => !empty($input['is_suspend']) ? 1 : 0,
            'is_recurring' => !empty($input['is_recurring']) ? $input['is_recurring'] : 0,
            'recur_interval' => !empty($input['recur_interval']) ? $input['recur_interval'] : 1,
            'recur_interval_type' => !empty($input['recur_interval_type']) ? $input['recur_interval_type'] : null,
            'subscription_repeat_on' => !empty($input['subscription_repeat_on']) ? $input['subscription_repeat_on'] : null,
            'subscription_no' => !empty($input['subscription_no']) ? $input['subscription_no'] : null,
            'recur_repetitions' => !empty($input['recur_repetitions']) ? $input['recur_repetitions'] : 0,
            'order_addresses' => !empty($input['order_addresses']) ? $input['order_addresses'] : null,
            'sub_type' => !empty($input['sub_type']) ? $input['sub_type'] : null,
            'rp_earned' => $input['status'] == 'final' ? $this->calculateRewardPoints($business_id, $final_total) : 0,
            'rp_redeemed' => !empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0,
            'rp_redeemed_amount' => !empty($input['rp_redeemed_amount']) ? $input['rp_redeemed_amount'] : 0,
            'is_created_from_api' => !empty($input['is_created_from_api']) ? 1 : 0,
            'types_of_service_id' => !empty($input['types_of_service_id']) ? $input['types_of_service_id'] : null,
            'packing_charge' => !empty($input['packing_charge']) ? $input['packing_charge'] : 0,
            'packing_charge_type' => !empty($input['packing_charge_type']) ? $input['packing_charge_type'] : null,
            'service_custom_field_1' => !empty($input['service_custom_field_1']) ? $input['service_custom_field_1'] : null,
            'service_custom_field_2' => !empty($input['service_custom_field_2']) ? $input['service_custom_field_2'] : null,
            'service_custom_field_3' => !empty($input['service_custom_field_3']) ? $input['service_custom_field_3'] : null,
            'service_custom_field_4' => !empty($input['service_custom_field_4']) ? $input['service_custom_field_4'] : null,
            'service_custom_field_5' => !empty($input['service_custom_field_5']) ? $input['service_custom_field_5'] : null,
            'service_custom_field_6' => !empty($input['service_custom_field_6']) ? $input['service_custom_field_6'] : null,
            'round_off_amount' => !empty($input['round_off_amount']) ? $input['round_off_amount'] : 0,
            'import_batch' => !empty($input['import_batch']) ? $input['import_batch'] : null,
            'import_time' => !empty($input['import_time']) ? $input['import_time'] : null,
            'res_table_id' => !empty($input['res_table_id']) ? $input['res_table_id'] : null,
            'res_waiter_id' => !empty($input['res_waiter_id']) ? $input['res_waiter_id'] : null,
            'sales_order_ids' => !empty($input['sales_order_ids']) ? $input['sales_order_ids'] : null,
            'prefer_payment_method' => !empty($input['prefer_payment_method']) ? $input['prefer_payment_method'] : null,
            'prefer_payment_account' => !empty($input['prefer_payment_account']) ? $input['prefer_payment_account'] : null,
            'is_export' => !empty($input['is_export']) ? 1 : 0,
            'export_custom_fields_info' => (!empty($input['is_export']) && !empty($input['export_custom_fields_info'])) ? $input['export_custom_fields_info'] : null,
            'additional_expense_value_1' => isset($input['additional_expense_value_1']) ? $uf_data ? $this->num_uf($input['additional_expense_value_1']) : $input['additional_expense_value_1'] : 0,
            'additional_expense_value_2' => isset($input['additional_expense_value_2']) ? $uf_data ? $this->num_uf($input['additional_expense_value_2']) : $input['additional_expense_value_2'] : 0,
            'additional_expense_value_3' => isset($input['additional_expense_value_3']) ? $uf_data ? $this->num_uf($input['additional_expense_value_3']) : $input['additional_expense_value_3'] : 0,
            'additional_expense_value_4' => isset($input['additional_expense_value_4']) ? $uf_data ? $this->num_uf($input['additional_expense_value_4']) : $input['additional_expense_value_4'] : 0,
            'additional_expense_key_1' => !empty($input['additional_expense_key_1']) ? $input['additional_expense_key_1'] : null,
            'additional_expense_key_2' => !empty($input['additional_expense_key_2']) ? $input['additional_expense_key_2'] : null,
            'additional_expense_key_3' => !empty($input['additional_expense_key_3']) ? $input['additional_expense_key_3'] : null,
            'additional_expense_key_4' => !empty($input['additional_expense_key_4']) ? $input['additional_expense_key_4'] : null,

        ]);

        return $transaction;
    }

    /**
     * Add Sell transaction
     *
     * @param mixed $transaction_id
     * @param int $business_id
     * @param array $input
     * @param float $invoice_total
     * @param int $user_id
     *
     * @return boolean
     */
    public function updateSellTransaction($transaction_id, $business_id, $input, $invoice_total, $user_id, $uf_data = true, $change_invoice_number = true)
    {
        $transaction = $transaction_id;

        if (!is_object($transaction)) {
            $transaction = Transaction::where('id', $transaction_id)
                        ->where('business_id', $business_id)
                        ->firstOrFail();
        }
        
        //Update invoice number if changed from draft to finalize or vice-versa
        $invoice_no = $transaction->invoice_no;
        if ($transaction->status != $input['status'] && $change_invoice_number) {
            $invoice_scheme_id = !empty($input['invoice_scheme_id']) ? $input['invoice_scheme_id'] : null;
            $invoice_no = $this->getInvoiceNumber($business_id, $input['status'], $transaction->location_id, $invoice_scheme_id);
        }
        $final_total = $uf_data ? $this->num_uf($input['final_total']) : $input['final_total'];
        $update_date = [
            'status' => $input['status'],
            'invoice_no' => !empty($input['invoice_no']) ? $input['invoice_no'] : $invoice_no,
            'contact_id' => $input['contact_id'],
            'customer_group_id' => $input['customer_group_id'],
            'total_before_tax' => $invoice_total['total_before_tax'],
            'tax_id' => $input['tax_rate_id'],
            'discount_type' => $input['discount_type'],
            'discount_amount' => $uf_data ? $this->num_uf($input['discount_amount']) : $input['discount_amount'],
            'tax_amount' => $invoice_total['tax'],
            'final_total' => $final_total,
            'document' => isset($input['document']) ? $input['document'] : $transaction->document,
            'source' => isset($input['source']) ? $input['source'] : $transaction->source,
            'additional_notes' => !empty($input['sale_note']) ? $input['sale_note'] : null,
            'staff_note' => !empty($input['staff_note']) ? $input['staff_note'] : null,
            'custom_field_1' => !empty($input['custom_field_1']) ? $input['custom_field_1'] : null,
            'custom_field_2' => !empty($input['custom_field_2']) ? $input['custom_field_2'] : null,
            'custom_field_3' => !empty($input['custom_field_3']) ? $input['custom_field_3'] : null,
            'custom_field_4' => !empty($input['custom_field_4']) ? $input['custom_field_4'] : null,
            'commission_agent' => $input['commission_agent'],
            'is_quotation' => isset($input['is_quotation']) ? $input['is_quotation'] : 0,
            'sub_status' => !empty($input['sub_status']) ? $input['sub_status'] : null,
            'shipping_details' => isset($input['shipping_details']) ? $input['shipping_details'] : null,
            'shipping_charges' => isset($input['shipping_charges']) ? $uf_data ? $this->num_uf($input['shipping_charges']) : $input['shipping_charges'] : 0,
            'shipping_address' => isset($input['shipping_address']) ? $input['shipping_address'] : null,
            'shipping_status' => isset($input['shipping_status']) ? $input['shipping_status'] : null,
            'delivered_to' => isset($input['delivered_to']) ? $input['delivered_to'] : null,
            'shipping_custom_field_1' => !empty($input['shipping_custom_field_1']) ? $input['shipping_custom_field_1'] : null,
            'shipping_custom_field_2' => !empty($input['shipping_custom_field_2']) ? $input['shipping_custom_field_2'] : null,
            'shipping_custom_field_3' => !empty($input['shipping_custom_field_3']) ? $input['shipping_custom_field_3'] : null,
            'shipping_custom_field_4' => !empty($input['shipping_custom_field_4']) ? $input['shipping_custom_field_4'] : null,
            'shipping_custom_field_5' => !empty($input['shipping_custom_field_5']) ? $input['shipping_custom_field_5'] : null,
            'exchange_rate' => !empty($input['exchange_rate']) ?
                                $uf_data ? $this->num_uf($input['exchange_rate']) : $input['exchange_rate'] : 1,
            'selling_price_group_id' => isset($input['selling_price_group_id']) ? $input['selling_price_group_id'] : null,
            'pay_term_number' => isset($input['pay_term_number']) ? $input['pay_term_number'] : null,
            'pay_term_type' => isset($input['pay_term_type']) ? $input['pay_term_type'] : null,
            'is_suspend' => !empty($input['is_suspend']) ? 1 : 0,
            'is_recurring' => !empty($input['is_recurring']) ? $input['is_recurring'] : 0,
            'recur_interval' => !empty($input['recur_interval']) ? $input['recur_interval'] : 1,
            'recur_interval_type' => !empty($input['recur_interval_type']) ? $input['recur_interval_type'] : null,
            'subscription_repeat_on' => !empty($input['subscription_repeat_on']) ? $input['subscription_repeat_on'] : null,
            'recur_repetitions' => !empty($input['recur_repetitions']) ? $input['recur_repetitions'] : 0,
            'order_addresses' => !empty($input['order_addresses']) ? $input['order_addresses'] : null,
            'rp_earned' => $input['status'] == 'final' ? $this->calculateRewardPoints($business_id, $final_total) : 0,
            'rp_redeemed' => !empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0,
            'rp_redeemed_amount' => !empty($input['rp_redeemed_amount']) ? $input['rp_redeemed_amount'] : 0,
            'types_of_service_id' => !empty($input['types_of_service_id']) ? $input['types_of_service_id'] : null,
            'packing_charge' => !empty($input['packing_charge']) ? $input['packing_charge'] : 0,
            'packing_charge_type' => !empty($input['packing_charge_type']) ? $input['packing_charge_type'] : null,
            'service_custom_field_1' => !empty($input['service_custom_field_1']) ? $input['service_custom_field_1'] : null,
            'service_custom_field_2' => !empty($input['service_custom_field_2']) ? $input['service_custom_field_2'] : null,
            'service_custom_field_3' => !empty($input['service_custom_field_3']) ? $input['service_custom_field_3'] : null,
            'service_custom_field_4' => !empty($input['service_custom_field_4']) ? $input['service_custom_field_4'] : null,
            'service_custom_field_5' => !empty($input['service_custom_field_5']) ? $input['service_custom_field_5'] : null,
            'service_custom_field_6' => !empty($input['service_custom_field_6']) ? $input['service_custom_field_6'] : null,
            'round_off_amount' => !empty($input['round_off_amount']) ? $input['round_off_amount'] : 0,
            'res_table_id' => !empty($input['res_table_id']) ? $input['res_table_id'] : null,
            'res_waiter_id' => !empty($input['res_waiter_id']) ? $input['res_waiter_id'] : null,
            'sales_order_ids' => !empty($input['sales_order_ids']) ? $input['sales_order_ids'] : null,
            'prefer_payment_method' => !empty($input['prefer_payment_method']) ? $input['prefer_payment_method'] : null,
            'prefer_payment_account' => !empty($input['prefer_payment_account']) ? $input['prefer_payment_account'] : null,
            'is_export' => !empty($input['is_export']) ? 1 : 0,
            'export_custom_fields_info' => (!empty($input['is_export']) && !empty($input['export_custom_fields_info'])) ? $input['export_custom_fields_info'] : null,
            'additional_expense_value_1' => isset($input['additional_expense_value_1']) ? $uf_data ? $this->num_uf($input['additional_expense_value_1']) : $input['additional_expense_value_1'] : 0,
            'additional_expense_value_2' => isset($input['additional_expense_value_2']) ? $uf_data ? $this->num_uf($input['additional_expense_value_2']) : $input['additional_expense_value_2'] : 0,
            'additional_expense_value_3' => isset($input['additional_expense_value_3']) ? $uf_data ? $this->num_uf($input['additional_expense_value_3']) : $input['additional_expense_value_3'] : 0,
            'additional_expense_value_4' => isset($input['additional_expense_value_4']) ? $uf_data ? $this->num_uf($input['additional_expense_value_4']) : $input['additional_expense_value_4'] : 0,
            'additional_expense_key_1' => !empty($input['additional_expense_key_1']) ? $input['additional_expense_key_1'] : null,
            'additional_expense_key_2' => !empty($input['additional_expense_key_2']) ? $input['additional_expense_key_2'] : null,
            'additional_expense_key_3' => !empty($input['additional_expense_key_3']) ? $input['additional_expense_key_3'] : null,
            'additional_expense_key_4' => !empty($input['additional_expense_key_4']) ? $input['additional_expense_key_4'] : null,
        ];

        if (!empty($input['transaction_date'])) {
            $update_date['transaction_date'] = $input['transaction_date'];
        }
        
        $transaction->fill($update_date);
        $transaction->update();

        return $transaction;
    }

    /**
     * Add/Edit transaction sell lines
     *
     * @param object/int $transaction
     * @param array $products
     * @param array $location_id
     * @param boolean $return_deleted = false
     * @param array $extra_line_parameters = []
     *   Example: ['database_trasnaction_linekey' => 'products_line_key'];
     *
     * @return boolean/object
     */
    public function createOrUpdateSellLines($transaction, $products, $location_id, $return_deleted = false, $status_before = null, $extra_line_parameters = [], $uf_data = true)
    {
        $lines_formatted = [];
        $modifiers_array = [];
        $edit_ids = [0];
        $modifiers_formatted = [];
        $combo_lines = [];
        $products_modified_combo = [];
        foreach ($products as $product) {
            $multiplier = 1;
            if (isset($product['sub_unit_id']) && $product['sub_unit_id'] == $product['product_unit_id']) {
                unset($product['sub_unit_id']);
            }

            if (!empty($product['sub_unit_id']) && !empty($product['base_unit_multiplier'])) {
                $multiplier = $product['base_unit_multiplier'];
            }

            //Check if transaction_sell_lines_id is set, used when editing.
            if (!empty($product['transaction_sell_lines_id'])) {
                $edit_id_temp = $this->editSellLine($product, $location_id, $status_before, $multiplier, $uf_data);
                $edit_ids = array_merge($edit_ids, $edit_id_temp);

                //update or create modifiers for existing sell lines
                if ($this->isModuleEnabled('modifiers')) {
                    if (!empty($product['modifier'])) {
                        foreach ($product['modifier'] as $key => $value) {
                            if (!empty($product['modifier_sell_line_id'][$key])) {
                                $edit_modifier = TransactionSellLine::find($product['modifier_sell_line_id'][$key]);
                                $edit_modifier->quantity = isset($product['modifier_quantity'][$key]) ? $product['modifier_quantity'][$key] : 1;
                                $modifiers_formatted[] = $edit_modifier;
                                //Dont delete modifier sell line if exists
                                $edit_ids[] = $product['modifier_sell_line_id'][$key];
                            } else {
                                if (!empty($product['modifier_price'][$key])) {
                                    $this_price = $uf_data ? $this->num_uf($product['modifier_price'][$key]) : $product['modifier_price'][$key];
                                    $modifier_quantity = isset($product['modifier_quantity'][$key]) ? $product['modifier_quantity'][$key] : 1;
                                    $modifiers_formatted[] = new TransactionSellLine([
                                        'product_id' => $product['modifier_set_id'][$key],
                                        'variation_id' => $value,
                                        'quantity' => $modifier_quantity,
                                        'unit_price_before_discount' => $this_price,
                                        'unit_price' => $this_price,
                                        'unit_price_inc_tax' => $this_price,
                                        'parent_sell_line_id' => $product['transaction_sell_lines_id'],
                                        'children_type' => 'modifier'
                                    ]);
                                }
                            }
                        }
                    }
                }
            } else {
                $products_modified_combo[] = $product;

                //calculate unit price and unit price before discount
                $uf_unit_price = $uf_data ? $this->num_uf($product['unit_price']) : $product['unit_price'];
                $unit_price_before_discount = $uf_unit_price / $multiplier;
                $unit_price = $unit_price_before_discount;
                if (!empty($product['line_discount_type']) && $product['line_discount_amount']) {
                    $discount_amount = $uf_data ? $this->num_uf($product['line_discount_amount']) : $product['line_discount_amount'];
                    if ($product['line_discount_type'] == 'fixed') {

                        //Note: Consider multiplier for fixed discount amount
                        $unit_price = $unit_price_before_discount - ($discount_amount/$multiplier);
                    } elseif ($product['line_discount_type'] == 'percentage') {
                        $unit_price = ((100 - $discount_amount) * $unit_price_before_discount) / 100;
                    }
                }
                $uf_quantity = $uf_data ? $this->num_uf($product['quantity']) : $product['quantity'];
                $uf_item_tax = $uf_data ?$this->num_uf($product['item_tax']) : $product['item_tax'];
                $uf_unit_price_inc_tax = $uf_data ? $this->num_uf($product['unit_price_inc_tax']) : $product['unit_price_inc_tax'];

                $line_discount_amount = 0;
                if (!empty($product['line_discount_amount'])) {
                    $line_discount_amount = $uf_data ? $this->num_uf($product['line_discount_amount']) : $product['line_discount_amount'];  

                    if ($product['line_discount_type'] == 'fixed') {
                        $line_discount_amount = $line_discount_amount/$multiplier;
                    }
                }

                $line = [
                    'product_id' => $product['product_id'],
                    'variation_id' => $product['variation_id'],
                    'quantity' =>  $uf_quantity * $multiplier,
                    'unit_price_before_discount' => $unit_price_before_discount,
                    'unit_price' => $unit_price,
                    'line_discount_type' => !empty($product['line_discount_type']) ? $product['line_discount_type'] : null,
                    'line_discount_amount' => $line_discount_amount,
                    'item_tax' =>  $uf_item_tax / $multiplier,
                    'tax_id' => $product['tax_id'],
                    'unit_price_inc_tax' =>  $uf_unit_price_inc_tax / $multiplier,
                    'sell_line_note' => !empty($product['sell_line_note']) ? $product['sell_line_note'] : '',
                    'sub_unit_id' => !empty($product['sub_unit_id']) ? $product['sub_unit_id'] : null,
                    'discount_id' => !empty($product['discount_id']) ? $product['discount_id'] : null,
                    'res_service_staff_id' => !empty($product['res_service_staff_id']) ? $product['res_service_staff_id'] : null,
                    'res_line_order_status' => !empty($product['res_service_staff_id']) ? 'received' : null,
                    'so_line_id' => !empty($product['so_line_id']) ? $product['so_line_id'] : null
                ];

                foreach ($extra_line_parameters as $key => $value) {
                    $line[$key] = isset($product[$value]) ? $product[$value] : '';
                }

                if (!empty($product['lot_no_line_id'])) {
                    $line['lot_no_line_id'] = $product['lot_no_line_id'];
                }

                //Check if restaurant module is enabled then add more data related to that.
                if ($this->isModuleEnabled('modifiers')) {
                    $sell_line_modifiers = [];

                    if (!empty($product['modifier'])) {
                        foreach ($product['modifier'] as $key => $value) {
                            if (!empty($product['modifier_price'][$key])) {
                                $this_price = $uf_data ? $this->num_uf($product['modifier_price'][$key]) : $product['modifier_price'][$key];
                                $modifier_quantity = isset($product['modifier_quantity'][$key]) ? $product['modifier_quantity'][$key] : 1;
                                $sell_line_modifiers[] = [
                                    'product_id' => $product['modifier_set_id'][$key],
                                    'variation_id' => $value,
                                    'quantity' => $modifier_quantity,
                                    'unit_price_before_discount' => $this_price,
                                    'unit_price' => $this_price,
                                    'unit_price_inc_tax' => $this_price,
                                    'children_type' => 'modifier'
                                ];
                            }
                        }
                    }
                    $modifiers_array[] = $sell_line_modifiers;
                }

                $lines_formatted[] = new TransactionSellLine($line);

                $sell_line_warranties[] = !empty($product['warranty_id']) ? $product['warranty_id'] : 0;

                //Update purchase order line quantity received
                $this->updateSalesOrderLine($line['so_line_id'], $line['quantity'], 0);
            }
        }

        if (!is_object($transaction)) {
            $transaction = Transaction::findOrFail($transaction);
        }

        //Delete the products removed and increment product stock.
        $deleted_lines = [];
        if (!empty($edit_ids)) {
            $deleted_lines = TransactionSellLine::where('transaction_id', $transaction->id)
                    ->whereNotIn('id', $edit_ids)
                    ->select('id')->get()->toArray();
            $combo_delete_lines = TransactionSellLine::whereIn('parent_sell_line_id', $deleted_lines)->where('children_type', 'combo')->select('id')->get()->toArray();
            $deleted_lines = array_merge($deleted_lines, $combo_delete_lines);

            $adjust_qty = $status_before == 'draft' ? false : true;

            $this->deleteSellLines($deleted_lines, $location_id, $adjust_qty);
        }

        $combo_lines = [];

        if (!empty($lines_formatted)) {
            $transaction->sell_lines()->saveMany($lines_formatted);

            //Add corresponding modifier sell lines if exists
            if ($this->isModuleEnabled('modifiers')) {
                foreach ($lines_formatted as $key => $value) {
                    if (!empty($modifiers_array[$key])) {
                        foreach ($modifiers_array[$key] as $modifier) {
                            $modifier['parent_sell_line_id'] = $value->id;
                            $modifiers_formatted[] = new TransactionSellLine($modifier);
                        }
                    }
                }
            }

            //Combo product lines.
            //$products_value = array_values($products);
            foreach ($lines_formatted as $key => $value) {
                if (!empty($products_modified_combo[$key]['product_type']) && $products_modified_combo[$key]['product_type'] == 'combo') {
                    $combo_lines = array_merge($combo_lines, $this->__makeLinesForComboProduct($products_modified_combo[$key]['combo'], $value));
                }

                //Save sell line warranty if set
                if (!empty($sell_line_warranties[$key])) {
                    $value->warranties()->sync([$sell_line_warranties[$key]]);
                }
            }
        }

        if (!empty($combo_lines)) {
            $transaction->sell_lines()->saveMany($combo_lines);
        }

        if (!empty($modifiers_formatted)) {
            $transaction->sell_lines()->saveMany($modifiers_formatted);
        }

        if ($return_deleted) {
            return $deleted_lines;
        }
        return true;
    }

    private function updateSalesOrderLine($so_line_id, $new_qty, $old_qty = 0)
    {
        $diff = $new_qty - $old_qty;
        if (!empty($so_line_id) && !empty($diff)) {
            $so_line = TransactionSellLine::find($so_line_id);
            $so_line->so_quantity_invoiced += ($diff);
            $so_line->save();
        }
    }

    /**
     * Returns the line for combo product
     *
     * @param array $combo_items
     * @param object $parent_sell_line
     *
     * @return array
     */
    private function __makeLinesForComboProduct($combo_items, $parent_sell_line)
    {
        $combo_lines = [];

        //Calculate the percentage change in price.
        $combo_total_price = 0;
        foreach ($combo_items as $key => $value) {
            $sell_price_inc_tax = Variation::findOrFail($value['variation_id'])->sell_price_inc_tax;

            $combo_items[$key]['unit_price_inc_tax'] = $sell_price_inc_tax;
            $combo_total_price += $value['quantity'] * $sell_price_inc_tax;
        }
        $change_percent = $this->get_percent($combo_total_price, $parent_sell_line->unit_price_inc_tax * $parent_sell_line->quantity);

        foreach ($combo_items as $value) {
            $price = $this->calc_percentage($value['unit_price_inc_tax'], $change_percent, $value['unit_price_inc_tax']);

            $combo_lines[] = new TransactionSellLine([
                'product_id' => $value['product_id'],
                'variation_id' => $value['variation_id'],
                'quantity' => $value['quantity'],
                'unit_price_before_discount' => $price,
                'unit_price' => $price,
                'line_discount_type' => null,
                'line_discount_amount' => 0,
                'item_tax' => 0,
                'tax_id' => null,
                'unit_price_inc_tax' => $price,
                'sub_unit_id' => null,
                'discount_id' => null,
                'parent_sell_line_id' => $parent_sell_line->id,
                'children_type' => 'combo'
            ]);
        }

        return $combo_lines;
    }

    /**
     * Edit transaction sell line
     *
     * @param array $product
     * @param int $location_id
     *
     * @return boolean
     */
    public function editSellLine($product, $location_id, $status_before, $multiplier = 1, $uf_data = true)
    {
        //Get the old order quantity
        $sell_line = TransactionSellLine::with(['product', 'warranties'])
                    ->find($product['transaction_sell_lines_id']);

        $old_qty = $sell_line->quantity;
        $edit_ids[] = $product['transaction_sell_lines_id'];
        //Adjust quanity
        if ($status_before != 'draft') {
            $new_qty = $this->num_uf($product['quantity']) * $multiplier;
            $difference = $sell_line->quantity - $new_qty;
            $this->adjustQuantity($location_id, $product['product_id'], $product['variation_id'], $difference);
        }

        $uf_unit_price = $uf_data ? $this->num_uf($product['unit_price']) : $product['unit_price'];
        $unit_price_before_discount = $uf_unit_price / $multiplier;
        $unit_price = $unit_price_before_discount;
        if (!empty($product['line_discount_type']) && $product['line_discount_amount']) {
            $discount_amount = $uf_data ? $this->num_uf($product['line_discount_amount']) : $product['line_discount_amount'];
            if ($product['line_discount_type'] == 'fixed') {
                $unit_price = $unit_price_before_discount - ($discount_amount/$multiplier);
            } elseif ($product['line_discount_type'] == 'percentage') {
                $unit_price = ((100 - $discount_amount) * $unit_price_before_discount) / 100;
            }
        }
        $line_discount_amount = 0;
        if (!empty($product['line_discount_amount'])) {
            $line_discount_amount = $uf_data ? $this->num_uf($product['line_discount_amount']) : $product['line_discount_amount'];  

            if ($product['line_discount_type'] == 'fixed') {
                $line_discount_amount = $line_discount_amount/$multiplier;
            }
        }

        //Update sell lines.
        $sell_line->fill(['product_id' => $product['product_id'],
            'variation_id' => $product['variation_id'],
            'quantity' => $uf_data ? $this->num_uf($product['quantity']) * $multiplier : $product['quantity'] * $multiplier,
            'unit_price_before_discount' => $unit_price_before_discount,
            'unit_price' => $unit_price,
            'line_discount_type' => !empty($product['line_discount_type']) ? $product['line_discount_type'] : null,
            'line_discount_amount' => $line_discount_amount,
            'item_tax' => $uf_data ? $this->num_uf($product['item_tax']) / $multiplier : $product['item_tax'] / $multiplier,
            'tax_id' => $product['tax_id'],
            'unit_price_inc_tax' => $uf_data ? $this->num_uf($product['unit_price_inc_tax']) / $multiplier : $product['unit_price_inc_tax'] / $multiplier,
            'sell_line_note' => !empty($product['sell_line_note']) ? $product['sell_line_note'] : '',
            'sub_unit_id' => !empty($product['sub_unit_id']) ? $product['sub_unit_id'] : null,
            'res_service_staff_id' => !empty($product['res_service_staff_id']) ? $product['res_service_staff_id'] : null
        ]);
        $sell_line->save();

        //Set warranty
        if (!empty($product['warranty_id'])) {
            $warranty_ids = $sell_line->warranties->pluck('warranty_id')->toArray();
            if (!in_array($product['warranty_id'], $warranty_ids)) {
                $warranty_ids[] = $product['warranty_id'];
                $sell_line->warranties()->sync(array_filter($warranty_ids));
            }
        } else {
            $sell_line->warranties()->sync([]);
        }

        //Adjust the sell line for combo items.
        if (isset($product['product_type']) && $product['product_type'] == 'combo' && !empty($product['combo'])) {
            //$this->editSellLineCombo($sell_line, $location_id, $sell_line->quantity, $new_qty);

            //Assign combo product sell line to $edit_ids so that it will not get deleted
            foreach ($product['combo'] as $combo_line) {
                $edit_ids[] = $combo_line['transaction_sell_lines_id'];
            }

            $adjust_stock = ($status_before != 'draft');
            $this->updateEditedSellLineCombo($product['combo'], $location_id, $adjust_stock);
        }

        $this->updateSalesOrderLine($sell_line->so_line_id, $sell_line->quantity, $old_qty);

        return $edit_ids;
    }

    /**
     * Delete the products removed and increment product stock.
     *
     * @param array $transaction_line_ids
     * @param int $location_id
     *
     * @return boolean
     */
    public function deleteSellLines($transaction_line_ids, $location_id, $adjust_qty = true)
    {
        if (!empty($transaction_line_ids)) {
            $sell_lines = TransactionSellLine::whereIn('id', $transaction_line_ids)
                        ->get();

            //Adjust quanity
            
            foreach ($sell_lines as $line) {
                if ($adjust_qty) {
                    $this->adjustQuantity($location_id, $line->product_id, $line->variation_id, $line->quantity);
                }

                //Update purchase order line quantity received
                $this->updateSalesOrderLine($line->so_line_id, 0, $line->quantity);
            }

            //unset so_line_id if set
            TransactionSellLine::whereIn('so_line_id', $transaction_line_ids)
                    ->update(['so_line_id' => null]); 
            
            TransactionSellLine::whereIn('id', $transaction_line_ids)
                ->delete();
        }
    }

    /**
     * Adjust the quantity of product and its variation
     *
     * @param int $location_id
     * @param int $product_id
     * @param int $variation_id
     * @param float $increment_qty
     *
     * @return boolean
     */
    private function adjustQuantity($location_id, $product_id, $variation_id, $increment_qty)
    {
        if ($increment_qty != 0) {
            $enable_stock = Product::find($product_id)->enable_stock;

            if ($enable_stock == 1) {
                //Adjust Quantity in variations location table
                VariationLocationDetails::where('variation_id', $variation_id)
                ->where('product_id', $product_id)
                ->where('location_id', $location_id)
                ->increment('qty_available', $increment_qty);
            }
        }
    }

    /**
     * Add line for payment
     *
     * @param object/int $transaction
     * @param array $payments
     *
     * @return boolean
     */
    public function createOrUpdatePaymentLines($transaction, $payments, $business_id = null, $user_id = null, $uf_data = true)
    {
        $payments_formatted = [];
        $edit_ids = [0];
        $account_transactions = [];
        
        if (!is_object($transaction)) {
            $transaction = Transaction::findOrFail($transaction);
        }

        //If status is draft don't add payment
        if ($transaction->status == 'draft') {
            return true;
        }
        $c = 0;
        $prefix_type = 'sell_payment';
        if ($transaction->type == 'purchase') {
            $prefix_type = 'purchase_payment';
        }
        $contact_balance = Contact::where('id', $transaction->contact_id)->value('balance');
        foreach ($payments as $payment) {
            //Check if transaction_sell_lines_id is set.
            if (!empty($payment['payment_id'])) {
                $edit_ids[] = $payment['payment_id'];
                $this->editPaymentLine($payment, $transaction, $uf_data);
            } else {
                $payment_amount = $uf_data ? $this->num_uf($payment['amount']) : $payment['amount'];
                if ($payment['method'] == 'advance' && $payment_amount > $contact_balance) {
                    throw new AdvanceBalanceNotAvailable(__('lang_v1.required_advance_balance_not_available'));
                }
                //If amount is 0 then skip.
                if ($payment_amount != 0) {
                    $prefix_type = 'sell_payment';
                    if ($transaction->type == 'purchase') {
                        $prefix_type = 'purchase_payment';
                    }
                    $ref_count = $this->setAndGetReferenceCount($prefix_type, $business_id);
                    //Generate reference number
                    $payment_ref_no = $this->generateReferenceNumber($prefix_type, $ref_count, $business_id);

                    //If change return then set account id same as the first payment line account id
                    if (isset($payment['is_return']) && $payment['is_return'] == 1) {
                        $payment['account_id'] = !empty($payments[0]['account_id']) ? $payments[0]['account_id'] : null;
                    }

                    if (!empty($payment['paid_on'])) {
                        $paid_on = $uf_data ? $this->uf_date($payment['paid_on'], true) : $payment['paid_on'];
                    } else {
                        $paid_on = \Carbon::now()->toDateTimeString();
                    }
                    
                    $payment_data = [
                        'amount' => $payment_amount,
                        'method' => $payment['method'],
                        'business_id' => $transaction->business_id,
                        'is_return' => isset($payment['is_return']) ? $payment['is_return'] : 0,
                        'card_transaction_number' => isset($payment['card_transaction_number']) ? $payment['card_transaction_number'] : null,
                        'card_number' => isset($payment['card_number']) ? $payment['card_number'] : null,
                        'card_type' => isset($payment['card_type']) ? $payment['card_type'] : null,
                        'card_holder_name' => isset($payment['card_holder_name']) ? $payment['card_holder_name'] : null,
                        'card_month' => isset($payment['card_month']) ? $payment['card_month'] : null,
                        'card_security' => isset($payment['card_security']) ? $payment['card_security'] : null,
                        'cheque_number' => isset($payment['cheque_number']) ? $payment['cheque_number'] : null,
                        'bank_account_number' => isset($payment['bank_account_number']) ? $payment['bank_account_number'] : null,
                        'note' => isset($payment['note']) ? $payment['note'] : null,
                        'paid_on' => $paid_on,
                        'created_by' => empty($user_id) ? auth()->user()->id : $user_id,
                        'payment_for' => $transaction->contact_id,
                        'payment_ref_no' => $payment_ref_no,
                        'account_id' => !empty($payment['account_id']) && $payment['method'] != 'advance' ? $payment['account_id'] : null
                    ];

                    for ($i=1; $i<8; $i++) { 
                        if ($payment['method'] == 'custom_pay_' . $i) {
                            $payment_data['transaction_no'] = $payment["transaction_no_{$i}"];
                        }
                    }

                    $payments_formatted[] = new TransactionPayment($payment_data);

                    $account_transactions[$c] = [];

                    //create account transaction
                    $payment_data['transaction_type'] = $transaction->type;
                    $account_transactions[$c] = $payment_data;

                    $c++;
                }
            }
        }

        //Delete the payment lines removed.
        if (!empty($edit_ids)) {
            $deleted_transaction_payments = $transaction->payment_lines()->whereNotIn('id', $edit_ids)->get();

            $transaction->payment_lines()->whereNotIn('id', $edit_ids)->delete();

            //Fire delete transaction payment event
            foreach ($deleted_transaction_payments as $deleted_transaction_payment) {
                event(new TransactionPaymentDeleted($deleted_transaction_payment));
            }
        }
        
        if (!empty($payments_formatted)) {
            $transaction->payment_lines()->saveMany($payments_formatted);

            foreach ($transaction->payment_lines as $key => $value) {
                if (!empty($account_transactions[$key])) {
                    event(new TransactionPaymentAdded($value, $account_transactions[$key]));
                }
            }
        }

        return true;
    }

    /**
     * Edit transaction payment line
     *
     * @param array $product
     *
     * @return boolean
     */
    public function editPaymentLine($payment, $transaction = null, $uf_data = true)
    {
        $payment_id = $payment['payment_id'];
        unset($payment['payment_id']);

        for ($i=1; $i<8; $i++) { 
            if ($payment['method'] == 'custom_pay_' . $i) {
                $payment['transaction_no'] = $payment["transaction_no_{$i}"];
            }
            unset($payment["transaction_no_{$i}"]);
        }
        
        if (!empty($payment['paid_on'])) {
            $payment['paid_on'] = $uf_data ? $this->uf_date($payment['paid_on'], true) : $payment['paid_on'];
        }
                    
        $payment['amount'] = $uf_data ? $this->num_uf($payment['amount']) : $payment['amount'];

        $tp = TransactionPayment::where('id', $payment_id)
                            ->first();

        $transaction_type = !empty($transaction->type) ? $transaction->type : null;
        
        $tp->update($payment);

        //event
        event(new TransactionPaymentUpdated($tp, $transaction->type));

        return true;
    }

    /**
     * Get payment line for a transaction
     *
     * @param int $transaction_id
     *
     * @return boolean
     */
    public function getPaymentDetails($transaction_id)
    {
        $payment_lines = TransactionPayment::where('transaction_id', $transaction_id)
                    ->get()->toArray();

        return $payment_lines;
    }

    /**
     * Gives the receipt details in proper format.
     *
     * @param int $transaction_id
     * @param int $location_id
     * @param object $invoice_layout
     * @param array $business_details
     * @param array $receipt_details
     * @param string $receipt_printer_type
     *
     * @return array
     */
    public function getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type)
    {
        $il = $invoice_layout;

        $transaction = Transaction::find($transaction_id);
        $transaction_type = $transaction->type;

        $output = [
            'header_text' => isset($il->header_text) ? $il->header_text : '',
            'business_name' => ($il->show_business_name == 1) ? $business_details->name : '',
            'location_name' => ($il->show_location_name == 1) ? $location_details->name : '',
            'sub_heading_line1' => trim($il->sub_heading_line1),
            'sub_heading_line2' => trim($il->sub_heading_line2),
            'sub_heading_line3' => trim($il->sub_heading_line3),
            'sub_heading_line4' => trim($il->sub_heading_line4),
            'sub_heading_line5' => trim($il->sub_heading_line5),
            'table_product_label' => $il->table_product_label,
            'table_qty_label' => $il->table_qty_label,
            'table_unit_price_label' => $il->table_unit_price_label,
            'table_subtotal_label' => $il->table_subtotal_label,
        ];

        //Display name
        $output['display_name'] = $output['business_name'];
        if (!empty($output['location_name'])) {
            if (!empty($output['display_name'])) {
                $output['display_name'] .= ', ';
            }
            $output['display_name'] .= $output['location_name'];
        }

        //Codes
        if (!empty($business_details->code_label_1) && !empty($business_details->code_1)) {
            $output['code_label_1'] = $business_details->code_label_1;
            $output['code_1'] = $business_details->code_1;
        }

        if (!empty($business_details->code_label_1) && !empty($business_details->code_1)) {
            $output['code_label_2'] = $business_details->code_label_2;
            $output['code_2'] = $business_details->code_2;
        }
        
        //Logo
        $output['logo'] = $il->show_logo != 0 && !empty($il->logo) && file_exists(public_path('uploads/invoice_logos/' . $il->logo)) ? asset('uploads/invoice_logos/' . $il->logo) : false;

        //Address
        $output['address'] = '';
        $temp = [];
        if ($il->show_landmark == 1) {
            $temp[] = $location_details->landmark;
        }
        if ($il->show_city == 1 &&  !empty($location_details->city)) {
            $temp[] = $location_details->city;
        }
        if ($il->show_state == 1 && !empty($location_details->state)) {
            $temp[] = $location_details->state;
        }
        if ($il->show_zip_code == 1 &&  !empty($location_details->zip_code)) {
            $temp[] = $location_details->zip_code;
        }
        if ($il->show_country == 1 &&  !empty($location_details->country)) {
            $temp[] = $location_details->country;
        }
        if (!empty($temp)) {
            $output['address'] .= implode(', ', $temp);
        }

        $output['website'] = $location_details->website;
        $output['location_custom_fields'] = '';
        $temp = [];
        $location_custom_field_settings = !empty($il->location_custom_fields) ? $il->location_custom_fields : [];
        if (!empty($location_details->custom_field1) && in_array('custom_field1', $location_custom_field_settings)) {
            $temp[] = $location_details->custom_field1;
        }
        if (!empty($location_details->custom_field2) && in_array('custom_field2', $location_custom_field_settings)) {
            $temp[] = $location_details->custom_field2;
        }
        if (!empty($location_details->custom_field3) && in_array('custom_field3', $location_custom_field_settings)) {
            $temp[] = $location_details->custom_field3;
        }
        if (!empty($location_details->custom_field4) && in_array('custom_field4', $location_custom_field_settings)) {
            $temp[] = $location_details->custom_field4;
        }
        if (!empty($temp)) {
            $output['location_custom_fields'] .= implode(', ', $temp);
        }

        //Tax Info
        if ($il->show_tax_1 == 1 && !empty($business_details->tax_number_1)) {
            $output['tax_label1'] = !empty($business_details->tax_label_1) ? $business_details->tax_label_1 . ': ' : '';

            $output['tax_info1'] = $business_details->tax_number_1;
        }
        if ($il->show_tax_2 == 1 && !empty($business_details->tax_number_2)) {
            if (!empty($output['tax_info1'])) {
                $output['tax_info1'] .= ', ';
            }

            $output['tax_label2'] = !empty($business_details->tax_label_2) ? $business_details->tax_label_2 . ': ' : '';

            $output['tax_info2'] = $business_details->tax_number_2;
        }

        //Shop Contact Info
        $output['contact'] = '';
        if ($il->show_mobile_number == 1 && !empty($location_details->mobile)) {
            $output['contact'] .= '<b>' . __('contact.mobile') . ':</b> ' . $location_details->mobile;
        }
        if ($il->show_alternate_number == 1 && !empty($location_details->alternate_number)) {
            if (empty($output['contact'])) {
                $output['contact'] .= __('contact.mobile') . ': ' . $location_details->alternate_number;
            } else {
                $output['contact'] .= ', ' . $location_details->alternate_number;
            }
        }
        if ($il->show_email == 1 && !empty($location_details->email)) {
            if (!empty($output['contact'])) {
                $output['contact'] .= "\n";
            }
            $output['contact'] .= '<br>' . __('business.email') . ': ' . $location_details->email;
        }

        //Customer show_customer
        $customer = Contact::find($transaction->contact_id);

        $output['customer_info'] = '';
        $output['customer_tax_number'] = '';
        $output['customer_tax_label'] = '';
        $output['customer_custom_fields'] = '';
        if ($il->show_customer == 1) {
            $output['customer_label'] = !empty($il->customer_label) ? $il->customer_label : '';
            $output['customer_name'] = !empty($customer->name) ? $customer->name: $customer->supplier_business_name;
            $output['customer_mobile'] = $customer->mobile;
            
            if ($receipt_printer_type != 'printer') {
                $output['customer_info'] .= $customer->contact_address;
                if (!empty($customer->contact_address)) {
                    $output['customer_info'] .= '<br>';
                }
                $output['customer_info'] .= '<b>' . __('contact.mobile') . '</b>: ' . $customer->mobile;
                if (!empty($customer->landline)) {
                    $output['customer_info'] .= ', ' . $customer->landline;
                }
            }

            $output['customer_tax_number'] = $customer->tax_number;
            $output['customer_tax_label'] = !empty($il->client_tax_label) ? $il->client_tax_label : '';

            $temp = [];
            $customer_custom_fields_settings = !empty($il->contact_custom_fields) ? $il->contact_custom_fields : [];
            $contact_custom_labels = $this->getCustomLabels($business_details, 'contact');
            if (!empty($customer->custom_field1) && in_array('custom_field1', $customer_custom_fields_settings)) {

                if (!empty($contact_custom_labels['custom_field_1'])) {
                    $temp[] = $contact_custom_labels['custom_field_1'] . ': ' .$customer->custom_field1;
                } else {
                    $temp[] = $customer->custom_field1;
                }
            }
            if (!empty($customer->custom_field2) && in_array('custom_field2', $customer_custom_fields_settings)) {
                if (!empty($contact_custom_labels['custom_field_2'])) {
                    $temp[] = $contact_custom_labels['custom_field_2'] . ': ' .$customer->custom_field2;
                } else {
                    $temp[] = $customer->custom_field2;
                }
            }
            if (!empty($customer->custom_field3) && in_array('custom_field3', $customer_custom_fields_settings)) {
                if (!empty($contact_custom_labels['custom_field_3'])) {
                    $temp[] = $contact_custom_labels['custom_field_3'] . ': ' .$customer->custom_field3;
                } else {
                    $temp[] = $customer->custom_field3;
                }
            }
            if (!empty($customer->custom_field4) && in_array('custom_field4', $customer_custom_fields_settings)) {
                if (!empty($contact_custom_labels['custom_field_4'])) {
                    $temp[] = $contact_custom_labels['custom_field_4'] . ': ' .$customer->custom_field4;
                } else {
                    $temp[] = $customer->custom_field1;
                }
            }
            if (!empty($temp)) {
                $output['customer_custom_fields'] .= implode('<br>', $temp);
            }

            //To be used in pdfs
            $customer_address = [];
            if (!empty($customer->supplier_business_name)) {
                $customer_address[] = $customer->supplier_business_name;
            }
            if (!empty($customer->address_line_1)) {
                $customer_address[] = '<br>' . $customer->address_line_1;
            }
            if (!empty($customer->address_line_2)) {
                $customer_address[] =  '<br>' . $customer->address_line_2;
            }
            if (!empty($customer->city)) {
                $customer_address[] = '<br>' . $customer->city;
            }
            if (!empty($customer->state)) {
                $customer_address[] = $customer->state;
            }
            if (!empty($customer->country)) {
                $customer_address[] = $customer->country;
            }
            if (!empty($customer->zip_code)) {
                $customer_address[] = '<br>' . $customer->zip_code;
            }
            if (!empty(trim($customer->name))) {
                $customer_address[] = '<br>' . $customer->name;
            }
            if (!empty($customer->mobile)) {
                $customer_address[] = '<br>' .$customer->mobile;
            }
            if (!empty($customer->landline)) {
                $customer_address[] = $customer->landline;
            }

            $output['customer_info_address'] = '';
            if (!empty($customer_address)) {
                $output['customer_info_address'] = implode(', ', $customer_address);
            }
        }

        if ($il->show_reward_point == 1) {
            $output['customer_rp_label'] = $business_details->rp_name;
            $output['customer_total_rp'] = $customer->total_rp;
        }

        $output['client_id'] = '';
        $output['client_id_label'] = '';
        if ($il->show_client_id == 1) {
            $output['client_id_label'] = !empty($il->client_id_label) ? $il->client_id_label : '';
            $output['client_id'] = !empty($customer->contact_id) ? $customer->contact_id : '';
        }

        //Sales person info
        $output['sales_person'] = '';
        $output['sales_person_label'] = '';
        if ($il->show_sales_person == 1) {
            $output['sales_person_label'] = !empty($il->sales_person_label) ? $il->sales_person_label : '';
            $output['sales_person'] = !empty($transaction->sales_person->user_full_name) ? $transaction->sales_person->user_full_name : '';
        }

        //commission agent info
        $output['commission_agent'] = '';
        $output['commission_agent_label'] = '';
        if ($il->show_commission_agent == 1) {
            $output['commission_agent_label'] = !empty($il->commission_agent_label) ? $il->commission_agent_label : '';
            $output['commission_agent'] = !empty($transaction->sale_commission_agent->user_full_name) ? $transaction->sale_commission_agent->user_full_name : '';
        }

        //Invoice info
        $output['invoice_no'] = $transaction->invoice_no;
        $output['invoice_no_prefix'] = $il->invoice_no_prefix;
        $output['shipping_address'] = !empty($transaction->shipping_address()) ? $transaction->shipping_address() : $transaction->shipping_address;

        //Heading & invoice label, when quotation use the quotation heading.
        if ($transaction_type == 'sell_return') {
            $output['invoice_heading'] = $il->cn_heading;
            $output['invoice_no_prefix'] = $il->cn_no_label;

            //Parent sell details(return_parent_id)
            $output['parent_invoice_no'] = Transaction::find($transaction->return_parent_id)->invoice_no;
            $output['parent_invoice_no_prefix'] = $il->invoice_no_prefix;

        } elseif ($transaction->status == 'draft' && $transaction->sub_status == 'proforma' && !empty($il->common_settings['proforma_heading']) ) {
            $output['invoice_heading'] = $il->common_settings['proforma_heading'];
        } elseif ($transaction->status == 'draft' && $transaction->is_quotation == 1) {
            $output['invoice_heading'] = $il->quotation_heading;
            $output['invoice_no_prefix'] = $il->quotation_no_prefix;
        } elseif ($transaction_type == 'sales_order') {
            $output['invoice_heading'] = !empty($il->common_settings['sales_order_heading']) ? $il->common_settings['sales_order_heading'] : __('lang_v1.sales_order');
            $output['invoice_no_prefix'] = $il->quotation_no_prefix;
        } else {
            $output['invoice_heading'] = $il->invoice_heading;
            if ($transaction->payment_status == 'paid' && !empty($il->invoice_heading_paid)) {
                $output['invoice_heading'] .= ' ' . $il->invoice_heading_paid;
            } elseif (in_array($transaction->payment_status, ['due', 'partial']) && !empty($il->invoice_heading_not_paid)) {
                $output['invoice_heading'] .= ' ' . $il->invoice_heading_not_paid;
            }
        }

        $output['date_label'] = $il->date_label;
        if (blank($il->date_time_format)) {
            $output['invoice_date'] = $this->format_date($transaction->transaction_date, true, $business_details);
        } else {
            $output['invoice_date'] = \Carbon::createFromFormat('Y-m-d H:i:s', $transaction->transaction_date)->format($il->date_time_format);
        }

        $output['transaction_date'] = $transaction->transaction_date;
        $output['date_time_format'] = $business_details->date_format;
        $output['currency_symbol'] = $business_details->currency_symbol;

        $output['hide_price'] = !empty($il->common_settings['hide_price']) ? true : false;

        if (!empty($il->common_settings['show_due_date']) && $transaction->payment_status != 'paid') {
            $output['due_date_label'] = !empty($il->common_settings['due_date_label']) ? $il->common_settings['due_date_label'] : '';
            $due_date = $transaction->due_date;
            if (!empty($due_date)) {
                if (blank($il->date_time_format)) {
                    $output['due_date'] = $this->format_date($due_date->toDateTimeString(), true, $business_details);
                } else {
                    $output['due_date'] = \Carbon::createFromFormat('Y-m-d H:i:s', $due_date->toDateTimeString())->format($il->date_time_format);
                }
            }
        }
        
        $show_currency = true;
        if ($receipt_printer_type == 'printer' && trim($business_details->currency_symbol) != '$') {
            $show_currency = false;
        }

        //Invoice product lines
        $is_lot_number_enabled = $business_details->enable_lot_number;
        $is_product_expiry_enabled = $business_details->enable_product_expiry;

        $output['lines'] = [];
        $total_exempt = 0;
        if (in_array($transaction_type, ['sell', 'sales_order'])) {
            $sell_line_relations = ['modifiers', 'sub_unit', 'warranties'];

            if ($is_lot_number_enabled == 1) {
                $sell_line_relations[] = 'lot_details';
            }

            $lines = $transaction->sell_lines()->whereNull('parent_sell_line_id')->with($sell_line_relations)->get();

            foreach ($lines as $key => $value) {
                if (!empty($value->sub_unit_id)) {
                    $formated_sell_line = $this->recalculateSellLineTotals($business_details->id, $value);

                    $lines[$key] = $formated_sell_line;
                }
            }

            $output['item_discount_label'] = $il->common_settings['item_discount_label'] ?? '';

            $output['discounted_unit_price_label'] = $il->common_settings['discounted_unit_price_label'] ?? '';

            $output['show_base_unit_details'] = !empty($il->common_settings['show_base_unit_details']);

            $output['tax_summary_label'] = $il->common_settings['tax_summary_label'] ?? '';
            $details = $this->_receiptDetailsSellLines($lines, $il, $business_details);
            
            $output['lines'] = $details['lines'];
            $output['taxes'] = [];
            $total_quantity = 0;
            $total_line_discount = 0;
            $total_line_taxes = 0.000;
            $subtotal_exc_tax = 0.000;
            foreach ($details['lines'] as $line) {
                if (!empty($line['group_tax_details'])) {
                    foreach ($line['group_tax_details'] as $tax_group_detail) {
                        if (!isset($output['taxes'][$tax_group_detail['name']])) {
                            $output['taxes'][$tax_group_detail['name']] = 0.00;
                        }
                        $output['taxes'][$tax_group_detail['name']] += $tax_group_detail['calculated_tax'];
                    }
                } elseif (!empty($line['tax_id'])) {
                    if (!isset($output['taxes'][$line['tax_name']])) {
                        $output['taxes'][$line['tax_name']] = 0.000;
                    }

                    $output['taxes'][$line['tax_name']] += ($line['tax_unformatted'] * $line['quantity_uf']);
                }

                if (!empty($line['tax_id']) && $line['tax_percent'] == 0) {
                    $total_exempt += $line['line_total_uf'];
                }
                $subtotal_exc_tax += $line['line_total_exc_tax_uf'];
                $total_quantity += $line['quantity_uf'];
                $total_line_discount += ($line['line_discount_uf'] * $line['quantity_uf']);
                $total_line_taxes += ($line['tax_unformatted'] * $line['quantity_uf']);
            }

            if (!empty($il->common_settings['total_quantity_label'])) {
                $output['total_quantity_label'] = $il->common_settings['total_quantity_label'];
                $output['total_quantity'] = $this->num_f($total_quantity, false, $business_details, true);
            }
            $output['subtotal_exc_tax'] = $this->num_f($subtotal_exc_tax, true, $business_details, true);

            $output['total_line_discount'] = !empty($total_line_discount) ? $this->num_f($total_line_discount, true, $business_details) : 0;
        } elseif ($transaction_type == 'sell_return') {
            $parent_sell = Transaction::find($transaction->return_parent_id);
            $lines = $parent_sell->sell_lines;

            foreach ($lines as $key => $value) {
                if (!empty($value->sub_unit_id)) {
                    $formated_sell_line = $this->recalculateSellLineTotals($business_details->id, $value);

                    $lines[$key] = $formated_sell_line;
                }
            }

            $details = $this->_receiptDetailsSellReturnLines($lines, $il, $business_details);
            $output['lines'] = $details['lines'];

            $output['taxes'] = [];
            foreach ($details['lines'] as $line) {
                if (!empty($line['group_tax_details'])) {
                    foreach ($line['group_tax_details'] as $tax_group_detail) {
                        if (!isset($output['taxes'][$tax_group_detail['name']])) {
                            $output['taxes'][$tax_group_detail['name']] = 0.00;
                        }
                        $output['taxes'][$tax_group_detail['name']] += $tax_group_detail['calculated_tax'];
                    }
                }
            }
        }

        //show cat code
        $output['show_cat_code'] = $il->show_cat_code;
        $output['cat_code_label'] = $il->cat_code_label;

        //Subtotal
        $output['subtotal_label'] = $il->sub_total_label . ':';
        $output['subtotal'] = ($transaction->total_before_tax != 0) ? $this->num_f($transaction->total_before_tax, $show_currency, $business_details) : 0;
        $output['subtotal_unformatted'] = ($transaction->total_before_tax != 0) ? $transaction->total_before_tax : 0;

        //round off
        $output['round_off_label'] = !empty($il->round_off_label) ? $il->round_off_label . ':' : __('lang_v1.round_off') . ':';
        $output['round_off'] = $this->num_f($transaction->round_off_amount, $show_currency, $business_details);
        $output['round_off_amount'] = $transaction->round_off_amount;
        $output['total_exempt'] = $this->num_f($total_exempt, $show_currency, $business_details);
        $output['total_exempt_uf'] = $total_exempt;

        $taxed_subtotal = $output['subtotal_unformatted'] -  $total_exempt;
        $output['taxed_subtotal'] = $this->num_f($taxed_subtotal, $show_currency, $business_details);

        //Discount
        $discount_amount = $this->num_f($transaction->discount_amount, $show_currency, $business_details);
        $output['line_discount_label'] = $invoice_layout->discount_label;
        $output['discount_label'] = $invoice_layout->discount_label;
        $output['discount_label'] .= ($transaction->discount_type == 'percentage') ? ' <small>(' .  $discount_amount . '%)</small> :' : '';

        if ($transaction->discount_type == 'percentage') {
            $discount = ($transaction->discount_amount/100) * $transaction->total_before_tax;
        } else {
            $discount = $transaction->discount_amount;
        }
        $output['discount'] = ($discount != 0) ? $this->num_f($discount, $show_currency, $business_details) : 0;

        //reward points
        if ($business_details->enable_rp == 1 && !empty($transaction->rp_redeemed)) {
            $output['reward_point_label'] = $business_details->rp_name;
            $output['reward_point_amount'] = $this->num_f($transaction->rp_redeemed_amount, $show_currency, $business_details);
        }

        //Format tax
        if (!empty($output['taxes'])) {
            $total_tax = 0.000;
            foreach ($output['taxes'] as $key => $value) {
                $total_tax += $value;

                $output['taxes'][$key] = $this->num_f($value, $show_currency, $business_details);
            }

            $output['taxes'][trans("lang_v1.total_tax")] = $this->num_f($total_tax,$show_currency, $business_details);
        }

        //Order Tax
        $tax = $transaction->tax;
        $output['tax_label'] = $invoice_layout->tax_label;
        $output['line_tax_label'] = $invoice_layout->tax_label;
        if (!empty($tax) && !empty($tax->name)) {
            $output['tax_label'] .= ' (' . $tax->name . ')';
        }
        $output['tax_label'] .= ':';
        $output['tax'] = ($transaction->tax_amount != 0) ? $this->num_f($transaction->tax_amount, $show_currency, $business_details) : 0.000;

        if ($transaction->tax_amount != 0 && $tax->is_tax_group) {
            $transaction_group_tax_details = $this->groupTaxDetails($tax, $transaction->tax_amount);

            $output['group_tax_details'] = [];
            foreach ($transaction_group_tax_details as $value) {
                $output['group_tax_details'][$value['name']] = $this->num_f($value['calculated_tax'], $show_currency, $business_details);
            }
        }

        //Shipping charges
        $output['shipping_charges'] = ($transaction->shipping_charges != 0) ? $this->num_f($transaction->shipping_charges, $show_currency, $business_details) : 0;
        $output['shipping_charges_label'] = trans("sale.shipping_charges");
        //Shipping details
        $output['shipping_details'] = $transaction->shipping_details;
        $output['delivered_to'] = $transaction->delivered_to;
        $output['shipping_details_label'] = trans("sale.shipping_details");
        $output['packing_charge_label'] = trans("lang_v1.packing_charge");
        $output['packing_charge'] = ($transaction->packing_charge != 0) ? $this->num_f($transaction->packing_charge, $show_currency, $business_details) : 0;

        //Total
        if ($transaction_type == 'sell_return') {
            $output['total_label'] = $invoice_layout->cn_amount_label . ':';
            $output['total'] = $this->num_f($transaction->final_total, $show_currency, $business_details);
        } else {
            $output['total_label'] = $invoice_layout->total_label . ':';
            $output['total'] = $this->num_f($transaction->final_total, $show_currency, $business_details);
        }
        if (!empty($il->common_settings['show_total_in_words'])) {
            $word_format = isset($il->common_settings['num_to_word_format']) ? $il->common_settings['num_to_word_format'] : 'international';
            $output['total_in_words'] = $this->numToWord($transaction->final_total, null, $word_format);
        }

        $output['total_unformatted'] = $transaction->final_total;
        
        //Paid & Amount due, only if final
        if ($transaction_type == 'sell' && $transaction->status == 'final') {
            $paid_amount = $this->getTotalPaid($transaction->id);
            $due = $transaction->final_total - $paid_amount;

            $output['total_paid'] = ($paid_amount == 0) ? 0 : $this->num_f($paid_amount, $show_currency, $business_details);
            $output['total_paid_label'] = $il->paid_label;
            $output['total_due'] = ($due == 0) ? 0 : $this->num_f($due, $show_currency, $business_details);
            $output['total_due_label'] = $il->total_due_label;

            if ($il->show_previous_bal == 1) {
                $all_due = $this->getContactDue($transaction->contact_id);
                if (!empty($all_due)) {
                    $output['all_bal_label'] = $il->prev_bal_label;
                    $output['all_due'] = $this->num_f($all_due, $show_currency, $business_details);
                }
            }

            //Get payment details
            $output['payments'] = [];
            if ($il->show_payments == 1) {
                $payments = $transaction->payment_lines->toArray();
                $payment_types = $this->payment_types($transaction->location_id, true);
                if (!empty($payments)) {
                    foreach ($payments as $value) {
                        $method = !empty($payment_types[$value['method']]) ? $payment_types[$value['method']] : '';
                        if ($value['method'] == 'cash') {
                            $output['payments'][] =
                                ['method' => $method . ($value['is_return'] == 1 ? ' (' . $il->change_return_label . ')(-)' : ''),
                                'amount' => $this->num_f($value['amount'], $show_currency, $business_details),
                                'date' => $this->format_date($value['paid_on'], false, $business_details)
                                ];
                            if ($value['is_return'] == 1) {
                            }
                        } elseif ($value['method'] == 'card') {
                            $output['payments'][] =
                                ['method' => $method . (!empty($value['card_transaction_number']) ? (', Transaction Number:' . $value['card_transaction_number']) : ''),
                                'amount' => $this->num_f($value['amount'], $show_currency, $business_details),
                                'date' => $this->format_date($value['paid_on'], false, $business_details)
                                ];
                        } elseif ($value['method'] == 'cheque') {
                            $output['payments'][] =
                                ['method' => $method . (!empty($value['cheque_number']) ? (', Cheque Number:' . $value['cheque_number']) : ''),
                                'amount' => $this->num_f($value['amount'], $show_currency, $business_details),
                                'date' => $this->format_date($value['paid_on'], false, $business_details)
                                ];
                        } elseif ($value['method'] == 'bank_transfer') {
                            $output['payments'][] =
                                ['method' => $method . (!empty($value['bank_account_number']) ? (', Account Number:' . $value['bank_account_number']) : ''),
                                'amount' => $this->num_f($value['amount'], $show_currency, $business_details),
                                'date' => $this->format_date($value['paid_on'], false, $business_details)
                                ];
                        } elseif ($value['method'] == 'advance') {
                            $output['payments'][] =
                                ['method' => $method,
                                'amount' => $this->num_f($value['amount'], $show_currency, $business_details),
                                'date' => $this->format_date($value['paid_on'], false, $business_details)
                                ];
                        } elseif ($value['method'] == 'other') {
                            $output['payments'][] =
                                ['method' => $method,
                                'amount' => $this->num_f($value['amount'], $show_currency, $business_details),
                                'date' => $this->format_date($value['paid_on'], false, $business_details)
                                ];
                        } 

                        for ($i=1; $i<8; $i++) { 
                            if ($value['method'] == "custom_pay_{$i}") {
                                $output['payments'][] =
                                    ['method' => $method . (!empty($value['transaction_no']) ? (', ' . trans("lang_v1.transaction_no") . ':' . $value['transaction_no']) : ''),
                                    'amount' => $this->num_f($value['amount'], $show_currency, $business_details),
                                    'date' => $this->format_date($value['paid_on'], false, $business_details)
                                    ];
                            }
                        }
                    }
                }
            }
        }

        $output['additional_expenses'] = [];

        if( !empty( $transaction->additional_expense_value_1 )  && !empty( $transaction->additional_expense_key_1 )) {
            $output['additional_expenses'][$transaction->additional_expense_key_1] = $this->num_f($transaction->additional_expense_value_1, $show_currency, $business_details);
        }
        if( !empty( $transaction->additional_expense_value_2 )  && !empty( $transaction->additional_expense_key_2 )) {
            $output['additional_expenses'][$transaction->additional_expense_key_2] = $this->num_f($transaction->additional_expense_value_2, $show_currency, $business_details);
        }
        if( !empty( $transaction->additional_expense_value_3 )  && !empty( $transaction->additional_expense_key_3 )) {
            $output['additional_expenses'][$transaction->additional_expense_key_3] = $this->num_f($transaction->additional_expense_value_3, $show_currency, $business_details);
        }
        if( !empty( $transaction->additional_expense_value_4 )  && !empty( $transaction->additional_expense_key_4 )) {
            $output['additional_expenses'][$transaction->additional_expense_key_4] = $this->num_f($transaction->additional_expense_value_4, $show_currency, $business_details);
        }

        //Check for barcode
        $output['barcode'] = ($il->show_barcode == 1) ? $transaction->invoice_no : false;

        //Additional notes
        $output['additional_notes'] = $transaction->additional_notes;
        $output['footer_text'] = $invoice_layout->footer_text;
        
        //Barcode related information.
        $output['show_barcode'] = !empty($il->show_barcode) ? true : false;

        if (in_array($transaction_type, ['sell', 'sales_order'])) {
            //Qr code related information.
            $output['show_qr_code'] = !empty($il->show_qr_code) ? true : false;
            
            $zatca_qr = !empty($il->common_settings['zatca_qr']) ? true : false;

            if($zatca_qr){
                $total_order_tax = $transaction->tax_amount + $total_line_taxes;
                $qr_code_text = $this->_zatca_qr_text($business_details->name, $business_details->tax_number_1, $transaction->transaction_date, $transaction->final_total, $total_order_tax);
            } else {
                $is_label_enabled = !empty($il->common_settings['show_qr_code_label']) ? true : false;
                $qr_code_details = [];
                $qr_code_fields = !empty($il->qr_code_fields) ? $il->qr_code_fields : [];

                if (in_array('business_name', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? __('business.business') . ': ' . $business_details->name : $business_details->name;
                }
                if (in_array('address', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? __('business.address') . ': ' . $location_details->name . ', ' .$output['address'] : $location_details->name . ' ' . str_replace(',', '', $output['address']);
                }
                if (in_array('tax_1', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? $business_details->tax_label_1 . ': ' . $business_details->tax_number_1 : $business_details->tax_number_1;
                }
                if (in_array('tax_2', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? $business_details->tax_label_2 . ' ' . $business_details->tax_number_2 : $business_details->tax_number_2;
                }
                if (in_array('invoice_no', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? $il->invoice_no_prefix . ': ' . $transaction->invoice_no : $transaction->invoice_no;
                }
                if (in_array('invoice_datetime', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? $output['date_label'] . ': ' . $output['invoice_date'] : $output['invoice_date'];
                }
                if (in_array('subtotal', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? $output['subtotal_label'] . ' ' . $output['subtotal'] : $output['subtotal'];
                }
                if (in_array('total_amount', $qr_code_fields)) {
                    $qr_code_details[] = $is_label_enabled ? $output['total_label'] . ' ' . $output['total'] : $output['total'];
                }
                if (in_array('total_tax', $qr_code_fields)) {
                    $total_order_tax = $transaction->tax_amount + $total_line_taxes;
                    $total_order_tax_formatted = $this->num_f($total_order_tax, $show_currency, $business_details);
                    $qr_code_details[] = $is_label_enabled ? __('sale.tax') . ': ' . $total_order_tax_formatted : $total_order_tax_formatted;
                }
                if (in_array('customer_name', $qr_code_fields)) {
                    $cust_label = $il->customer_label ?? __('contact.customer');
                    $qr_code_details[] = $is_label_enabled ? $cust_label . ': ' . $customer->full_name : $customer->full_name;
                }
                if (in_array('invoice_url', $qr_code_fields)) {
                    $qr_code_details[] = $this->getInvoiceUrl($transaction->id, $business_details->id);
                }

                $output['qr_code_details'] = $qr_code_details;

                $qr_code_text = $is_label_enabled ? implode(', ', $qr_code_details) : implode(' ', $qr_code_details);
            }

            if ($transaction->status == 'final') {
                $output['qr_code_text'] = $qr_code_text;
            }
        }
        //Module related information.
        $il->module_info = !empty($il->module_info) ? json_decode($il->module_info, true) : [];
        if (!empty($il->module_info['tables']) && $this->isModuleEnabled('tables')) {
            //Table label & info
            $output['table_label'] = null;
            $output['table'] = null;
            if (isset($il->module_info['tables']['show_table'])) {
                $output['table_label'] = !empty($il->module_info['tables']['table_label']) ? $il->module_info['tables']['table_label'] : '';
                if (!empty($transaction->res_table_id)) {
                    $table = ResTable::find($transaction->res_table_id);
                }
                
                //res_table_id
                $output['table'] = !empty($table->name) ? $table->name : '';
            }
        }

        if (!empty($il->module_info['types_of_service']) && $this->isModuleEnabled('types_of_service') && !empty($transaction->types_of_service_id)) {
            //Table label & info
            $output['types_of_service_label'] = null;
            $output['types_of_service'] = null;
            if (isset($il->module_info['types_of_service']['show_types_of_service'])) {
                $output['types_of_service_label'] = !empty($il->module_info['types_of_service']['types_of_service_label']) ? $il->module_info['types_of_service']['types_of_service_label'] : '';
                $output['types_of_service'] = $transaction->types_of_service->name;
            }

            if (isset($il->module_info['types_of_service']['show_tos_custom_fields'])) {
                $types_of_service_custom_labels = $this->getCustomLabels($business_details, 'types_of_service'); 
                $output['types_of_service_custom_fields'] = [];
                if (!empty($transaction->service_custom_field_1)) {
                    $tos_custom_label_1 = $types_of_service_custom_labels['custom_field_1'] ?? __('lang_v1.service_custom_field_1');
                    $output['types_of_service_custom_fields'][$tos_custom_label_1] = $transaction->service_custom_field_1;
                }
                if (!empty($transaction->service_custom_field_2)) {
                    $tos_custom_label_2 = $types_of_service_custom_labels['custom_field_2'] ?? __('lang_v1.service_custom_field_2');
                    $output['types_of_service_custom_fields'][$tos_custom_label_2] = $transaction->service_custom_field_2;
                }
                if (!empty($transaction->service_custom_field_3)) {
                    $tos_custom_label_3 = $types_of_service_custom_labels['custom_field_3'] ?? __('lang_v1.service_custom_field_3');
                    $output['types_of_service_custom_fields'][$tos_custom_label_3] = $transaction->service_custom_field_3;
                }
                if (!empty($transaction->service_custom_field_4)) {
                    $tos_custom_label_4 = $types_of_service_custom_labels['custom_field_4'] ?? __('lang_v1.service_custom_field_4');
                    $output['types_of_service_custom_fields'][$tos_custom_label_4] = $transaction->service_custom_field_4;
                }

                if (!empty($transaction->service_custom_field_5)) {
                    $tos_custom_label_5 = $types_of_service_custom_labels['custom_field_5'] ?? __('lang_v1.service_custom_field_5');
                    $output['types_of_service_custom_fields'][$tos_custom_label_5] = $transaction->service_custom_field_5;
                }

                if (!empty($transaction->service_custom_field_6)) {
                    $tos_custom_label_6 = $types_of_service_custom_labels['custom_field_6'] ?? __('lang_v1.service_custom_field_6');
                    $output['types_of_service_custom_fields'][$tos_custom_label_6] = $transaction->service_custom_field_6;
                }
            }
        }

        if (!empty($il->module_info['service_staff']) && $this->isModuleEnabled('service_staff')) {
            //Waiter label & info
            $output['service_staff_label'] = null;
            $output['service_staff'] = null;
            if (isset($il->module_info['service_staff']['show_service_staff'])) {
                $output['service_staff_label'] = !empty($il->module_info['service_staff']['service_staff_label']) ? $il->module_info['service_staff']['service_staff_label'] : '';
                if (!empty($transaction->res_waiter_id)) {
                    $waiter = \App\User::find($transaction->res_waiter_id);
                }
                
                //res_table_id
                $output['service_staff'] = !empty($waiter->id) ? implode(' ', [$waiter->first_name, $waiter->last_name]) : '';
            }
        }

        //Repair module details
        if (!empty($il->module_info['repair']) && $transaction->sub_type == 'repair') {
            if (!empty($il->module_info['repair']['show_repair_status'])) {
                $output['repair_status_label'] = $il->module_info['repair']['repair_status_label'];
                $output['repair_status'] = '';
                if (!empty($transaction->repair_status_id)) {
                    $repair_status = \Modules\Repair\Entities\RepairStatus::find($transaction->repair_status_id);
                    $output['repair_status'] = $repair_status->name;
                }
            }

            if (!empty($il->module_info['repair']['show_repair_warranty'])) {
                $output['repair_warranty_label'] = $il->module_info['repair']['repair_warranty_label'];
                $output['repair_warranty'] = '';
                if (!empty($transaction->repair_warranty_id)) {
                    $repair_warranty = \App\Warranty::find($transaction->repair_warranty_id);
                    $output['repair_warranty'] = $repair_warranty->name;
                }
            }

            if (!empty($il->module_info['repair']['show_serial_no'])) {
                $output['serial_no_label'] = $il->module_info['repair']['serial_no_label'];
                $output['repair_serial_no'] = $transaction->repair_serial_no;
            }

            if (!empty($il->module_info['repair']['show_defects'])) {
                $output['defects_label'] = $il->module_info['repair']['defects_label'];
                $output['repair_defects'] = $transaction->repair_defects;
            }

            if (!empty($il->module_info['repair']['show_model'])) {
                $output['model_no_label'] = $il->module_info['repair']['model_no_label'];

                $output['repair_model_no'] = '';

                if (!empty($transaction->repair_model_id)) {
                    $device_model = \Modules\Repair\Entities\DeviceModel::find($transaction->repair_model_id);

                    if (!empty($device_model)) {
                        $output['repair_model_no'] = $device_model->name;
                    }
                }
            }

            if (!empty($il->module_info['repair']['show_repair_checklist'])) {
                $output['repair_checklist_label'] = $il->module_info['repair']['repair_checklist_label'];
                $output['checked_repair_checklist'] = $transaction->repair_checklist;

                $checklists = [];
                if (!empty($transaction->repair_model_id)) {
                    $model = \Modules\Repair\Entities\DeviceModel::find($transaction->repair_model_id);

                    if (!empty($model) && !empty($model->repair_checklist)) {
                        $checklists = explode('|', $model->repair_checklist);
                    }
                }

                $output['repair_checklist'] = $checklists;
            }

            if (!empty($il->module_info['repair']['show_device'])) {
                $output['device_label'] = $il->module_info['repair']['device_label'];
                $device = \App\Category::find($transaction->repair_device_id);

                $output['repair_device'] = '';
                if (!empty($device)) {
                    $output['repair_device'] = $device->name;
                }
            }

            if (!empty($il->module_info['repair']['show_brand'])) {
                $output['brand_label'] = $il->module_info['repair']['brand_label'];
                $brand = \App\Brands::find($transaction->repair_brand_id);
                $output['repair_brand'] = '';
                if (!empty($brand)) {
                    $output['repair_brand'] = $brand->name;
                }
            }
        }

        //Custom fields
        $custom_labels = json_decode($business_details['custom_labels']);

        //shipping custom fields
        if (!empty($custom_labels->shipping->custom_field_1)) {
            $output['shipping_custom_field_1_label'] = $custom_labels->shipping->custom_field_1;
            $output['shipping_custom_field_1_value'] = $transaction['shipping_custom_field_1'];
        }

        if (!empty($custom_labels->shipping->custom_field_2)) {
            $output['shipping_custom_field_2_label'] = $custom_labels->shipping->custom_field_2;
            $output['shipping_custom_field_2_value'] = $transaction['shipping_custom_field_2'];
        }

        if (!empty($custom_labels->shipping->custom_field_3)) {
            $output['shipping_custom_field_3_label'] = $custom_labels->shipping->custom_field_3;
            $output['shipping_custom_field_3_value'] = $transaction['shipping_custom_field_3'];
        }

        if (!empty($custom_labels->shipping->custom_field_4)) {
            $output['shipping_custom_field_4_label'] = $custom_labels->shipping->custom_field_4;
            $output['shipping_custom_field_4_value'] = $transaction['shipping_custom_field_4'];
        }

        //Sell custom fields
        if (!empty($custom_labels->shipping->custom_field_5)) {
            $output['shipping_custom_field_5_label'] = $custom_labels->shipping->custom_field_5;
            $output['shipping_custom_field_5_value'] = $transaction['shipping_custom_field_5'];
        }
        
        if (!empty($custom_labels->sell->custom_field_1)) {
            $output['sell_custom_field_1_label'] = $custom_labels->sell->custom_field_1;
            $output['sell_custom_field_1_value'] = $transaction['custom_field_1'];
        }

        if (!empty($custom_labels->sell->custom_field_2)) {
            $output['sell_custom_field_2_label'] = $custom_labels->sell->custom_field_2;
            $output['sell_custom_field_2_value'] = $transaction['custom_field_2'];
        }

        if (!empty($custom_labels->sell->custom_field_3)) {
            $output['sell_custom_field_3_label'] = $custom_labels->sell->custom_field_3;
            $output['sell_custom_field_3_value'] = $transaction['custom_field_3'];
        }

        if (!empty($custom_labels->sell->custom_field_4)) {
            $output['sell_custom_field_4_label'] = $custom_labels->sell->custom_field_4;
            $output['sell_custom_field_4_value'] = $transaction['custom_field_4'];
        }

        // location custom fields
        if (in_array('custom_field1', $location_custom_field_settings) && !empty($location_details->custom_field1) && !empty($custom_labels->location->custom_field_1)) {
            $output['location_custom_field_1_label'] = $custom_labels->location->custom_field_1;
            $output['location_custom_field_1_value'] = $location_details->custom_field1;
        }

        if (in_array('custom_field2', $location_custom_field_settings) && !empty($location_details->custom_field2) && !empty($custom_labels->location->custom_field_2)) {
            $output['location_custom_field_2_label'] = $custom_labels->location->custom_field_2;
            $output['location_custom_field_2_value'] = $location_details->custom_field2;
        }

        if (in_array('custom_field3', $location_custom_field_settings) && !empty($location_details->custom_field3) && !empty($custom_labels->location->custom_field_3)) {
            $output['location_custom_field_3_label'] = $custom_labels->location->custom_field_3;
            $output['location_custom_field_3_value'] = $location_details->custom_field3;
        }

        if (in_array('custom_field4', $location_custom_field_settings) && !empty($location_details->custom_field4) && !empty($custom_labels->location->custom_field_4)) {
            $output['location_custom_field_4_label'] = $custom_labels->location->custom_field_4;
            $output['location_custom_field_4_value'] = $location_details->custom_field4;
        }
        
        //Used in pdfs
        if (!empty($transaction->sales_order_ids)) {
            $sale_orders = Transaction::where('type', 'sales_order')
                            ->find($transaction->sales_order_ids);
                            
            $output['sale_orders_invoice_no'] = implode(', ', $sale_orders->pluck('invoice_no')->toArray());
            $sale_orders_invoice_date = [];
            foreach ($sale_orders as $sale_order) {
                if (blank($il->date_time_format)) {
                    $sale_orders_invoice_date[] = $this->format_date($sale_order->transaction_date, true);
                } else {
                    $sale_orders_invoice_date[] = \Carbon::createFromFormat('Y-m-d H:i:s', $sale_order->transaction_date)->format($il->date_time_format);
                }
            }
            $output['sale_orders_invoice_date'] = implode(', ', $sale_orders_invoice_date);
        }

        if (!empty($transaction->prefer_payment_method)) {
            $payment_types = $this->payment_types(null, true, $transaction->business_id);
            $output['preferred_payment_method'] = $payment_types[$transaction->prefer_payment_method];
        }

        if (!empty($transaction->prefer_payment_account)) {
            $output['preferred_account_details'] = $transaction->preferredAccount->account_details;
        }
        
        //export custom fields
        $output['is_export'] = $transaction->is_export;
        $export_custom_fields_info = $transaction->export_custom_fields_info;
        if (!empty($transaction->is_export)) {
            $output['export_custom_fields_info']['export_custom_field_1'] = $export_custom_fields_info['export_custom_field_1'] ?? '';
            $output['export_custom_fields_info']['export_custom_field_2'] = $export_custom_fields_info['export_custom_field_2'] ?? '';
            $output['export_custom_fields_info']['export_custom_field_3'] = $export_custom_fields_info['export_custom_field_3'] ?? '';
            $output['export_custom_fields_info']['export_custom_field_4'] = $export_custom_fields_info['export_custom_field_4'] ?? '';
            $output['export_custom_fields_info']['export_custom_field_5'] = $export_custom_fields_info['export_custom_field_5'] ?? '';
            $output['export_custom_fields_info']['export_custom_field_6'] = $export_custom_fields_info['export_custom_field_6'] ?? '';
        }
        
        $output['design'] = $il->design;
        $output['table_tax_headings'] = !empty($il->table_tax_headings) ? array_filter(json_decode($il->table_tax_headings), 'strlen') : null;
        return (object)$output;
    }

    /**
     * This QR code is used in saudi arabia, TLV format
     * https://github.com/SallaApp/ZATCA/blob/master/src/Tag.php
     * Need to validate the qr code from mobile app
     * @return string
     */
    protected function _zatca_qr_text($seller, $tax_number, $invoice_date, $invoice_total_amount, $invoice_tax_amount){
        $string = '';

        //$seller = 'Salla';
        //$tax_number = '1234567891';
        //$invoice_date = '2021-07-12T14:25:09Z';
        //$invoice_total_amount = '100.00';
        //$invoice_tax_amount = '15.00';

        $invoice_total_amount = round($invoice_total_amount, 2);
        //$invoice_date = \Carbon::parse($invoice_date)->toIso8601ZuluString();
        $invoice_date = \Carbon::parse($invoice_date)->toIso8601String();

        $string .= $this->toHex(1).$this->toHex(strlen($seller)).($seller);
        $string .= $this->toHex(2).$this->toHex(strlen($tax_number)).($tax_number);
        $string .= $this->toHex(3).$this->toHex(strlen($invoice_date)).($invoice_date);
        $string .= $this->toHex(4).$this->toHex(strlen($invoice_total_amount)).($invoice_total_amount);
        $string .= $this->toHex(5).$this->toHex(strlen($invoice_tax_amount)).($invoice_tax_amount);

        return base64_encode($string);
    }

    /**
     * To convert the string value to hex.
     *
     * @param $value
     *
     * @return false|string
     */
    protected function toHex($value)
    {
        return pack("H*", sprintf("%02X", $value));
    }

    /**
     * Returns each line details for sell invoice display
     *
     * @return array
     */
    protected function _receiptDetailsSellLines($lines, $il, $business_details)
    {
        $is_lot_number_enabled = $business_details->enable_lot_number;
        $is_product_expiry_enabled = $business_details->enable_product_expiry;

        $output_lines = [];
        //$output_taxes = ['taxes' => []];
        $product_custom_fields_settings = !empty($il->product_custom_fields) ? $il->product_custom_fields : [];

        $is_warranty_enabled = !empty($business_details->common_settings['enable_product_warranty']) ? true : false;

        foreach ($lines as $line) {
            $product = $line->product;
            $variation = $line->variations;
            $product_variation = $line->variations->product_variation;
            $unit = $line->product->unit;
            $brand = $line->product->brand;
            $cat = $line->product->category;
            $tax_details = TaxRate::find($line->tax_id);

            $unit_name = !empty($unit->short_name) ? $unit->short_name : '';
            $base_unit_name = $unit_name;
            $base_unit_multiplier = 1;
            if (!empty($line->sub_unit->short_name)) {
                $unit_name = $line->sub_unit->short_name;
            }
            $line_array = [
                //Field for 1st column
                'name' => $product->name,
                'variation' => (empty($variation->name) || $variation->name == 'DUMMY') ? '' : $variation->name,
                'product_variation' => (empty($product_variation->name) || $product_variation->name == 'DUMMY') ? '' : $product_variation->name,
                //Field for 2nd column
                'quantity' => $this->num_f($line->quantity, false, $business_details, true),
                'quantity_uf' => $line->quantity,
                'units' => $unit_name,

                'base_unit_name' => $base_unit_name,
                'base_unit_multiplier' => !empty($line->multiplier) && $line->multiplier != 1 ? $this->num_f($line->multiplier, false, $business_details) : 1,
                'orig_quantity' => $this->num_f($line->orig_quantity, false, $business_details, true),
                'unit_price' => $this->num_f($line->unit_price, false, $business_details),
                'unit_price_uf' => $line->unit_price,
                'tax' => $this->num_f($line->item_tax, false, $business_details),
                'tax_id' => $line->tax_id,
                'tax_unformatted' => $line->item_tax,
                'tax_name' => !empty($tax_details) ? $tax_details->name : null,
                'tax_percent' => !empty($tax_details) ? $tax_details->amount : null,

                //Field for 3rd column
                'unit_price_inc_tax' => $this->num_f($line->unit_price_inc_tax, false, $business_details),
                'unit_price_inc_tax_uf' => $line->unit_price_inc_tax,
                'unit_price_exc_tax' => $this->num_f($line->unit_price, false, $business_details),
                'price_exc_tax' => $line->quantity * $line->unit_price,
                'unit_price_before_discount' => $this->num_f($line->unit_price_before_discount, false, $business_details),
                'unit_price_before_discount_uf' => $line->unit_price_before_discount,
                //Fields for 4th column
                'line_total' => $this->num_f($line->unit_price_inc_tax * $line->quantity, false, $business_details),
                'line_total_uf' => $line->unit_price_inc_tax * $line->quantity,
                'line_total_exc_tax' => $this->num_f($line->unit_price * $line->quantity, false, $business_details),
                'line_total_exc_tax_uf' => $line->unit_price * $line->quantity
            ];

            $temp = [];
            
            if (!empty($product->product_custom_field1) && in_array('product_custom_field1', $product_custom_fields_settings)) {
                $temp[] = $product->product_custom_field1;
            }
            if (!empty($product->product_custom_field2) && in_array('product_custom_field2', $product_custom_fields_settings)) {
                $temp[] = $product->product_custom_field2;
            }
            if (!empty($product->product_custom_field3) && in_array('product_custom_field3', $product_custom_fields_settings)) {
                $temp[] = $product->product_custom_field3;
            }
            if (!empty($product->product_custom_field4) && in_array('product_custom_field4', $product_custom_fields_settings)) {
                $temp[] = $product->product_custom_field4;
            }
            if (!empty($temp)) {
                $line_array['product_custom_fields'] = implode(',', $temp);
            }

            //Group product taxes by name.
            if (!empty($tax_details)) {
                if ($tax_details->is_tax_group) {
                    $group_tax_details = $this->groupTaxDetails($tax_details, $line->quantity * $line->item_tax);

                    $line_array['group_tax_details'] = $group_tax_details;

                    foreach ($group_tax_details as $value) {
                        if (!isset($output_taxes['taxes'][$value['name']])) {
                            $output_taxes['taxes'][$value['name']] = 0;
                        }
                        $output_taxes['taxes'][$value['name']] += $value['amount'];
                    }
                }
                else {
                    $tax_name = $tax_details->name;
                    if (!isset($output_taxes['taxes'][$tax_name])) {
                        $output_taxes['taxes'][$tax_name] = 0;
                    }
                    $output_taxes['taxes'][$tax_name] += ($line->quantity * $line->item_tax);
                }
            }

            $line_array['line_discount'] = method_exists($line, 'get_discount_amount') ? $this->num_f($line->get_discount_amount(), false, $business_details) : 0;
            $line_array['line_discount_uf'] = method_exists($line, 'get_discount_amount') ? $line->get_discount_amount() : 0;

            if ($line->line_discount_type == 'percentage') {
                $line_array['line_discount'] .= ' (' . $this->num_f($line->line_discount_amount, false, $business_details) . '%)';

                $line_array['line_discount_percent'] = $this->num_f($line->line_discount_amount, false, $business_details);
            }

            $line_array['total_line_discount'] = $this->num_f($line_array['line_discount_uf'] * $line_array['quantity_uf'], false, $business_details);

            if ($il->show_brand == 1) {
                $line_array['brand'] = !empty($brand->name) ? $brand->name : '';
            }
            if ($il->show_sku == 1) {
                $line_array['sub_sku'] = !empty($variation->sub_sku) ? $variation->sub_sku : '' ;
            }
            if ($il->show_image == 1) {
                $media = $variation->media;
                if (count($media)) {
                    $first_img = $media->first();
                    $line_array['image'] = !empty($first_img->display_url) ? $first_img->display_url : asset('/img/default.png');
                } else {
                    $line_array['image'] = $product->image_url;
                }
            }
            if ($il->show_cat_code == 1) {
                $line_array['cat_code'] = !empty($cat->short_code) ? $cat->short_code : '';
            }
            if ($il->show_sale_description == 1) {
                $line_array['sell_line_note'] = !empty($line->sell_line_note) ? nl2br($line->sell_line_note) : '';
            }
            if ($is_lot_number_enabled == 1 && $il->show_lot == 1) {
                $line_array['lot_number'] = !empty($line->lot_details->lot_number) ? $line->lot_details->lot_number : null;
                $line_array['lot_number_label'] = __('lang_v1.lot');
            }

            if ($is_product_expiry_enabled == 1 && $il->show_expiry == 1) {
                $line_array['product_expiry'] = !empty($line->lot_details->exp_date) ? $this->format_date($line->lot_details->exp_date, false, $business_details) : null;
                $line_array['product_expiry_label'] = __('lang_v1.expiry');
            }

            //Set warranty data if enabled
            if ($is_warranty_enabled && !empty($line->warranties->first())) {
                $warranty = $line->warranties->first();
                if (!empty($il->common_settings['show_warranty_name'])) {
                    $line_array['warranty_name'] = $warranty->name;
                }
                if (!empty($il->common_settings['show_warranty_description'])) {
                    $line_array['warranty_description'] = $warranty->description;
                }
                if (!empty($il->common_settings['show_warranty_exp_date'])) {
                    $line_array['warranty_exp_date'] = $warranty->getEndDate($line->transaction->transaction_date);
                }
            }

            //If modifier is set set modifiers line to parent sell line
            if (!empty($line->modifiers)) {
                foreach ($line->modifiers as $modifier_line) {
                    $product = $modifier_line->product;
                    $variation = $modifier_line->variations;
                    $unit = $modifier_line->product->unit;
                    $brand = $modifier_line->product->brand;
                    $cat = $modifier_line->product->category;

                    $modifier_line_array = [
                        //Field for 1st column
                        'name' => $product->name,
                        'variation' => (empty($variation->name) || $variation->name == 'DUMMY') ? '' : $variation->name,
                        //Field for 2nd column
                        'quantity' => $this->num_f($modifier_line->quantity, false, $business_details),
                        'units' => !empty($unit->short_name) ? $unit->short_name : '',

                        //Field for 3rd column
                        'unit_price_inc_tax' => $this->num_f($modifier_line->unit_price_inc_tax, false, $business_details),
                        'unit_price_exc_tax' => $this->num_f($modifier_line->unit_price, false, $business_details),
                        'price_exc_tax' => $modifier_line->quantity * $modifier_line->unit_price,

                        //Fields for 4th column
                        'line_total' => $this->num_f($modifier_line->unit_price_inc_tax * $line->quantity, false, $business_details),
                    ];
                    
                    if ($il->show_sku == 1) {
                        $modifier_line_array['sub_sku'] = !empty($variation->sub_sku) ? $variation->sub_sku : '' ;
                    }
                    if ($il->show_cat_code == 1) {
                        $modifier_line_array['cat_code'] = !empty($cat->short_code) ? $cat->short_code : '';
                    }
                    if ($il->show_sale_description == 1) {
                        $modifier_line_array['sell_line_note'] = !empty($line->sell_line_note) ? nl2br($line->sell_line_note) : '';
                    }

                    $line_array['modifiers'][] = $modifier_line_array;
                }
            }

            $output_lines[] = $line_array;
        }

        return ['lines' => $output_lines];
    }

    /**
     * Returns each line details for sell return invoice display
     *
     * @return array
     */
    protected function _receiptDetailsSellReturnLines($lines, $il, $business_details)
    {
        $is_lot_number_enabled = $business_details->enable_lot_number;
        $is_product_expiry_enabled = $business_details->enable_product_expiry;

        $output_lines = [];
        $output_taxes = ['taxes' => []];
        foreach ($lines as $line) {
            //Group product taxes by name.
            $tax_details = TaxRate::find($line->tax_id);
            // if (!empty($tax_details)) {
            //     if ($tax_details->is_tax_group) {
            //         $group_tax_details = $this->groupTaxDetails($tax_details, $line->quantity_returned * $line->item_tax);
            //         foreach ($group_tax_details as $key => $value) {
            //             if (!isset($output_taxes['taxes'][$key])) {
            //                 $output_taxes['taxes'][$key] = 0;
            //             }
            //             $output_taxes['taxes'][$key] += $value;
            //         }
            //     } else {
            //         $tax_name = $tax_details->name;
            //         if (!isset($output_taxes['taxes'][$tax_name])) {
            //             $output_taxes['taxes'][$tax_name] = 0;
            //         }
            //         $output_taxes['taxes'][$tax_name] += ($line->quantity_returned * $line->item_tax);
            //     }
            // }

            $product = $line->product;
            $variation = $line->variations;
            $unit = $line->product->unit;
            $brand = $line->product->brand;
            $cat = $line->product->category;

            $unit_name = !empty($unit->short_name) ? $unit->short_name : '';
            if (!empty($line->sub_unit->short_name)) {
                $unit_name = $line->sub_unit->short_name;
            }

            $line_array = [
                //Field for 1st column
                'name' => $product->name,
                'variation' => (empty($variation->name) || $variation->name == 'DUMMY') ? '' : $variation->name,
                //Field for 2nd column
                'quantity' => $this->num_f($line->quantity_returned, false, $business_details, true),
                'units' => $unit_name,

                'unit_price' => $this->num_f($line->unit_price, false, $business_details),
                'tax' => $this->num_f($line->item_tax, false, $business_details),
                'tax_name' => !empty($tax_details) ? $tax_details->name: null,

                //Field for 3rd column
                'unit_price_inc_tax' => $this->num_f($line->unit_price_inc_tax, false, $business_details),
                'unit_price_exc_tax' => $this->num_f($line->unit_price, false, $business_details),

                //Fields for 4th column
                'line_total' => $this->num_f($line->unit_price_inc_tax * $line->quantity_returned, false, $business_details),
            ];
            $line_array['line_discount'] = 0;

            //Group product taxes by name.
            if (!empty($tax_details)) {
                if ($tax_details->is_tax_group) {
                    $group_tax_details = $this->groupTaxDetails($tax_details, $line->quantity * $line->item_tax);

                    $line_array['group_tax_details'] = $group_tax_details;

                    // foreach ($group_tax_details as $key => $value) {
                    //     if (!isset($output_taxes['taxes'][$key])) {
                    //         $output_taxes['taxes'][$key] = 0;
                    //     }
                    //     $output_taxes['taxes'][$key] += $value;
                    // }
                }
                // else {
                //     $tax_name = $tax_details->name;
                //     if (!isset($output_taxes['taxes'][$tax_name])) {
                //         $output_taxes['taxes'][$tax_name] = 0;
                //     }
                //     $output_taxes['taxes'][$tax_name] += ($line->quantity * $line->item_tax);
                // }
            }

            if ($il->show_brand == 1) {
                $line_array['brand'] = !empty($brand->name) ? $brand->name : '';
            }
            if ($il->show_sku == 1) {
                $line_array['sub_sku'] = !empty($variation->sub_sku) ? $variation->sub_sku : '' ;
            }
            if ($il->show_cat_code == 1) {
                $line_array['cat_code'] = !empty($cat->short_code) ? $cat->short_code : '';
            }
            if ($il->show_sale_description == 1) {
                $line_array['sell_line_note'] = !empty($line->sell_line_note) ? $line->sell_line_note : '';
            }
            // if ($is_lot_number_enabled == 1 && $il->show_lot == 1) {
            //     $line_array['lot_number'] = !empty($line->lot_details->lot_number) ? $line->lot_details->lot_number : null;
            //     $line_array['lot_number_label'] = __('lang_v1.lot');
            // }

            // if ($is_product_expiry_enabled == 1 && $il->show_expiry == 1) {
            //     $line_array['product_expiry'] = !empty($line->lot_details->exp_date) ? $this->format_date($line->lot_details->exp_date) : null;
            //     $line_array['product_expiry_label'] = __('lang_v1.expiry');
            // }

            $output_lines[] = $line_array;
        }

        return ['lines' => $output_lines, 'taxes' => $output_taxes];
    }

    /**
     * Gives the invoice number for a Final/Draft invoice
     *
     * @param int $business_id
     * @param string $status
     * @param string $location_id
     *
     * @return string
     */
    public function getInvoiceNumber($business_id, $status, $location_id, $invoice_scheme_id = null, $sale_type = null)
    {
        if ($status == 'final') {
            if (empty($invoice_scheme_id)) {
                $scheme = $this->getInvoiceScheme($business_id, $location_id);
            } else {
                $scheme = InvoiceScheme::where('business_id', $business_id)
                                        ->find($invoice_scheme_id);
            }
            
            if ($scheme->scheme_type == 'blank') {
                $prefix = $scheme->prefix;
            } else {
                $prefix = $scheme->prefix . date('Y') . config('constants.invoice_scheme_separator');
            }

            //Count
            $count = $scheme->start_number + $scheme->invoice_count;
            $count = str_pad($count, $scheme->total_digits, '0', STR_PAD_LEFT);

            //Prefix + count
            $invoice_no = $prefix . $count;

            //Increment the invoice count
            $scheme->invoice_count = $scheme->invoice_count + 1;
            $scheme->save();

            return $invoice_no;
        } else if ($status == 'draft') {
            $ref_count = $this->setAndGetReferenceCount('draft', $business_id);
            $invoice_no = $this->generateReferenceNumber('draft', $ref_count, $business_id);
            return $invoice_no;
        } else if ($sale_type == 'sales_order') {
            $ref_count = $this->setAndGetReferenceCount('sales_order', $business_id);
            $invoice_no = $this->generateReferenceNumber('sales_order', $ref_count, $business_id);
            return $invoice_no;
        }
        else {
            return Str::random(5);
        }
    }

    private function getInvoiceScheme($business_id, $location_id)
    {
        $scheme_id = BusinessLocation::where('business_id', $business_id)
                    ->where('id', $location_id)
                    ->first()
                    ->invoice_scheme_id;
        if (!empty($scheme_id) && $scheme_id != 0) {
            $scheme = InvoiceScheme::find($scheme_id);
        }

        //Check if scheme is not found then return default scheme
        if (empty($scheme)) {
            $scheme = InvoiceScheme::where('business_id', $business_id)
                    ->where('is_default', 1)
                    ->first();
        }

        return $scheme;
    }

    /**
     * Gives the list of products for a purchase transaction
     *
     * @param int $business_id
     * @param int $transaction_id
     *
     * @return array
     */
    public function getPurchaseProducts($business_id, $transaction_id)
    {
        $products = Transaction::join('purchase_lines as pl', 'transactions.id', '=', 'pl.transaction_id')
                            ->leftjoin('products as p', 'pl.product_id', '=', 'p.id')
                            ->leftjoin('variations as v', 'pl.variation_id', '=', 'v.id')
                            ->where('transactions.business_id', $business_id)
                            ->where('transactions.id', $transaction_id)
                            ->where('transactions.type', 'purchase')
                            ->select('p.id as product_id', 'p.name as product_name', 'v.id as variation_id', 'v.name as variation_name', 'pl.quantity as quantity', 'pl.exp_date', 'pl.lot_number')
                            ->get();
        return $products;
    }

    /**
     * Gives the total purchase amount for a business within the date range passed
     *
     * @param int $business_id
     * @param int $transaction_id
     *
     * @return array
     */
    public function getPurchaseTotals($business_id, $start_date = null, $end_date = null, $location_id = null, $user_id = null)
    {
        $query = Transaction::where('business_id', $business_id)
                        ->where('type', 'purchase')
                        ->select(
                            DB::raw('SUM(final_total) as final_total_sum'),
                            //DB::raw("SUM(final_total - tax_amount) as total_exc_tax"),
                            DB::raw("SUM((SELECT COALESCE(SUM(tp.amount), 0) FROM transaction_payments as tp WHERE tp.transaction_id=transactions.id)) as total_paid"),
                            DB::raw('SUM(total_before_tax) as total_before_tax_sum'),
                            DB::raw('SUM(shipping_charges) as total_shipping_charges'),
                            DB::raw("SUM(additional_expense_value_1 + additional_expense_value_2 + additional_expense_value_3 + additional_expense_value_4) as total_expense")
                        );
        
        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('transaction_date', '>=', $start_date)
                ->whereDate('transaction_date', '<=', $end_date);
        }

        if (empty($start_date) && !empty($end_date)) {
            $query->whereDate('transaction_date', '<=', $end_date);
        }

        //Filter by the location
        if (!empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        //Filter by the location
        if (!empty($user_id)) {
            $query->where('transactions.created_by', $user_id);
        }

        $purchase_details = $query->first();

        $output['total_purchase_inc_tax'] = $purchase_details->final_total_sum;
        //$output['total_purchase_exc_tax'] = $purchase_details->sum('total_exc_tax');
        $output['total_purchase_exc_tax'] = $purchase_details->total_before_tax_sum;
        $output['purchase_due'] = $purchase_details->final_total_sum -
                                    $purchase_details->total_paid;
        $output['total_shipping_charges'] = $purchase_details->total_shipping_charges;
        $output['total_additional_expense'] = $purchase_details->total_expense;

        return $output;
    }

    /**
     * Gives the total sell amount for a business within the date range passed
     *
     * @param int $business_id
     * @param int $transaction_id
     *
     * @return array
     */
    public function getSellTotals($business_id, $start_date = null, $end_date = null, $location_id = null, $created_by = null)
    {
        $query = Transaction::where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.status', 'final')
                    ->select(
                        DB::raw('SUM(final_total) as total_sell'),
                        DB::raw("SUM(final_total - tax_amount) as total_exc_tax"),
                        DB::raw('SUM(final_total - (SELECT COALESCE(SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)), 0) FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id) )  as total_due'),
                        DB::raw('SUM(total_before_tax) as total_before_tax'),
                        DB::raw('SUM(shipping_charges) as total_shipping_charges'),
                        DB::raw("SUM(additional_expense_value_1 + additional_expense_value_2 + additional_expense_value_3 + additional_expense_value_4) as total_expense")
                    );

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('transactions.transaction_date', '>=', $start_date)
                ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if (empty($start_date) && !empty($end_date)) {
            $query->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        //Filter by the location
        if (!empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        if (!empty($created_by)) {
            $query->where('transactions.created_by', $created_by);
        }

        $sell_details = $query->first();

        $output['total_sell_inc_tax'] = $sell_details->total_sell;
        //$output['total_sell_exc_tax'] = $sell_details->sum('total_exc_tax');
        $output['total_sell_exc_tax'] = $sell_details->total_before_tax;
        $output['invoice_due'] = $sell_details->total_due;
        $output['total_shipping_charges'] = $sell_details->total_shipping_charges;
        $output['total_additional_expense'] = $sell_details->total_expense;

        return $output;
    }

    /**
     * Gives the total input tax for a business within the date range passed
     *
     * @param int $business_id
     * @param string $start_date default null
     * @param string $end_date default null
     *
     * @return float
     */
    public function getInputTax($business_id, $start_date = null, $end_date = null, $location_id = null)
    {
        //Calculate purchase taxes
        $query1 = Transaction::where('transactions.business_id', $business_id)
                        ->leftjoin('tax_rates as T', 'transactions.tax_id', '=', 'T.id')
                        ->whereIn('type', ['purchase', 'purchase_return'])
                        ->whereNotNull('transactions.tax_id')
                        ->select(
                            DB::raw("SUM( IF(type='purchase', transactions.tax_amount, -1 * transactions.tax_amount) ) as transaction_tax"),
                            'T.name as tax_name',
                            'T.id as tax_id',
                            'T.is_tax_group'
                        );

        //Calculate purchase line taxes
        $query2 = Transaction::where('transactions.business_id', $business_id)
                        ->leftjoin('purchase_lines as pl', 'transactions.id', '=', 'pl.transaction_id')
                        ->leftjoin('tax_rates as T', 'pl.tax_id', '=', 'T.id')
                        ->where('type', 'purchase')
                        ->whereNotNull('pl.tax_id')
                        ->select(
                            DB::raw("SUM( (pl.quantity - pl.quantity_returned) * pl.item_tax ) as product_tax"),
                            'T.name as tax_name',
                            'T.id as tax_id',
                            'T.is_tax_group'
                        );

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query1->whereIn('transactions.location_id', $permitted_locations);
            $query2->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query1->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            $query2->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
        }

        if (!empty($location_id)) {
            $query1->where('transactions.location_id', $location_id);
            $query2->where('transactions.location_id', $location_id);
        }

        $transaction_tax_details = $query1->groupBy('T.id')
                                    ->get();

        $product_tax_details = $query2->groupBy('T.id')
                                    ->get();
        $tax_details = [];
        foreach ($transaction_tax_details as $transaction_tax) {
            $tax_details[$transaction_tax->tax_id]['tax_name'] = $transaction_tax->tax_name;
            $tax_details[$transaction_tax->tax_id]['tax_amount'] = $transaction_tax->transaction_tax;

            $tax_details[$transaction_tax->tax_id]['is_tax_group'] = false;
            if ($transaction_tax->is_tax_group == 1) {
                $tax_details[$transaction_tax->tax_id]['is_tax_group'] = true;
            }
        }

        foreach ($product_tax_details as $product_tax) {
            if (!isset($tax_details[$product_tax->tax_id])) {
                $tax_details[$product_tax->tax_id]['tax_name'] = $product_tax->tax_name;
                $tax_details[$product_tax->tax_id]['tax_amount'] = $product_tax->product_tax;

                $tax_details[$product_tax->tax_id]['is_tax_group'] = false;
                if ($product_tax->is_tax_group == 1) {
                    $tax_details[$product_tax->tax_id]['is_tax_group'] = true;
                }
            } else {
                $tax_details[$product_tax->tax_id]['tax_amount'] += $product_tax->product_tax;
            }
        }

        //If group tax add group tax details
        foreach ($tax_details as $key => $value) {
            if ($value['is_tax_group']) {
                $tax_details[$key]['group_tax_details'] = $this->groupTaxDetails($key, $value['tax_amount']);
            }
        }

        $output['tax_details'] = $tax_details;
        $output['total_tax'] = $transaction_tax_details->sum('transaction_tax') + $product_tax_details->sum('product_tax');

        return $output;
    }

    /**
     * Gives the total output tax for a business within the date range passed
     *
     * @param int $business_id
     * @param string $start_date default null
     * @param string $end_date default null
     *
     * @return float
     */
    public function getOutputTax($business_id, $start_date = null, $end_date = null, $location_id = null)
    {
        //Calculate sell taxes
        $query1 = Transaction::where('transactions.business_id', $business_id)
                        ->leftjoin('tax_rates as T', 'transactions.tax_id', '=', 'T.id')
                        ->whereIn('type', ['sell', 'sell_return'])
                        ->whereNotNull('transactions.tax_id')
                        ->where('transactions.status', '=', 'final')
                        ->select(
                            DB::raw("SUM( IF(type='sell', transactions.tax_amount, -1 * transactions.tax_amount) ) as transaction_tax"),
                            'T.name as tax_name',
                            'T.id as tax_id',
                            'T.is_tax_group'
                        );

        //Calculate sell line taxes
        $query2 = Transaction::where('transactions.business_id', $business_id)
                        ->leftjoin('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                        ->leftjoin('tax_rates as T', 'tsl.tax_id', '=', 'T.id')
                        ->where('type', 'sell')
                        ->whereNotNull('tsl.tax_id')
                        ->where('transactions.status', '=', 'final')
                        ->select(
                            DB::raw("SUM( (tsl.quantity - tsl.quantity_returned) * tsl.item_tax ) as product_tax"),
                            'T.name as tax_name',
                            'T.id as tax_id',
                            'T.is_tax_group'
                        );

        ///Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query1->whereIn('transactions.location_id', $permitted_locations);
            $query2->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query1->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            $query2->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
        }

        if (!empty($location_id)) {
            $query1->where('transactions.location_id', $location_id);
            $query2->where('transactions.location_id', $location_id);
        }

        $transaction_tax_details = $query1->groupBy('T.id')
                                    ->get();

        $product_tax_details = $query2->groupBy('T.id')
                                    ->get();
        $tax_details = [];
        foreach ($transaction_tax_details as $transaction_tax) {
            $tax_details[$transaction_tax->tax_id]['tax_name'] = $transaction_tax->tax_name;
            $tax_details[$transaction_tax->tax_id]['tax_amount'] = $transaction_tax->transaction_tax;

            $tax_details[$transaction_tax->tax_id]['is_tax_group'] = false;
            if ($transaction_tax->is_tax_group == 1) {
                $tax_details[$transaction_tax->tax_id]['is_tax_group'] = true;
            }
        }

        foreach ($product_tax_details as $product_tax) {
            if (!isset($tax_details[$product_tax->tax_id])) {
                $tax_details[$product_tax->tax_id]['tax_name'] = $product_tax->tax_name;
                $tax_details[$product_tax->tax_id]['tax_amount'] = $product_tax->product_tax;

                $tax_details[$product_tax->tax_id]['is_tax_group'] = false;
                if ($product_tax->is_tax_group == 1) {
                    $tax_details[$product_tax->tax_id]['is_tax_group'] = true;
                }
            } else {
                $tax_details[$product_tax->tax_id]['tax_amount'] += $product_tax->product_tax;
            }
        }

        //If group tax add group tax details
        // foreach ($tax_details as $key => $value) {
        //     if ($value['is_tax_group']) {
        //         $tax_details[$key]['group_tax_details'] = $this->groupTaxDetails($key, $value['tax_amount']);
        //     }
        // }

        $output['tax_details'] = $tax_details;
        $output['total_tax'] = $transaction_tax_details->sum('transaction_tax') + $product_tax_details->sum('product_tax');

        return $output;
    }

    /**
     * Gives the total expense tax for a business within the date range passed
     *
     * @param int $business_id
     * @param string $start_date default null
     * @param string $end_date default null
     *
     * @return float
     */
    public function getExpenseTax($business_id, $start_date = null, $end_date = null, $location_id = null)
    {
        //Calculate expense taxes
        $query = Transaction::where('transactions.business_id', $business_id)
                        ->leftjoin('tax_rates as T', 'transactions.tax_id', '=', 'T.id')
                        ->where('type', 'expense')
                        ->whereNotNull('transactions.tax_id')
                        ->select(
                            DB::raw("SUM(transactions.tax_amount) as transaction_tax"),
                            'T.name as tax_name',
                            'T.id as tax_id',
                            'T.is_tax_group'
                        );

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
        }

        if (!empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $transaction_tax_details = $query->groupBy('T.id')
                                    ->get();

        $tax_details = [];
        foreach ($transaction_tax_details as $transaction_tax) {
            $tax_details[$transaction_tax->tax_id]['tax_name'] = $transaction_tax->tax_name;
            $tax_details[$transaction_tax->tax_id]['tax_amount'] = $transaction_tax->transaction_tax;

            $tax_details[$transaction_tax->tax_id]['is_tax_group'] = false;
            if ($transaction_tax->is_tax_group == 1) {
                $tax_details[$transaction_tax->tax_id]['is_tax_group'] = true;
            }
        }

        //If group tax add group tax details
        foreach ($tax_details as $key => $value) {
            if ($value['is_tax_group']) {
                $tax_details[$key]['group_tax_details'] = $this->groupTaxDetails($key, $value['tax_amount']);
            }
        }

        $output['tax_details'] = $tax_details;
        $output['total_tax'] = $transaction_tax_details->sum('transaction_tax');

        return $output;
    }

    /**
    * Gives total sells of last 30 days day-wise
    *
    * @param int $business_id
    * @param array $filters
    *
    * @return Obj
    */
    public function getSellsLast30Days($business_id, $group_by_location = false)
    {
        $query = Transaction::leftjoin('transactions as SR', function ($join) {
            $join->on('SR.return_parent_id', '=', 'transactions.id')
                                    ->where('SR.type', 'sell_return');
        })
                            ->where('transactions.business_id', $business_id)
                            ->where('transactions.type', 'sell')
                            ->where('transactions.status', 'final')
                            ->whereBetween(DB::raw('date(transactions.transaction_date)'), [\Carbon::now()->subDays(30), \Carbon::now()]);

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        $query->select(
            DB::raw("DATE_FORMAT(transactions.transaction_date, '%Y-%m-%d') as date"),
            DB::raw("SUM( transactions.final_total - COALESCE(SR.final_total, 0) ) as total_sells")
        )
        ->groupBy(DB::raw('Date(transactions.transaction_date)'));

        if ($group_by_location) {
            $query->addSelect('transactions.location_id');
            $query->groupBy('transactions.location_id');
        }
        $sells = $query->get();

        if (!$group_by_location) {
            $sells = $sells->pluck('total_sells', 'date');
        }

        return $sells;
    }

    /**
    * Gives total sells of current FY month-wise
    *
    * @param int $business_id
    * @param string $start
    * @param string $end
    *
    * @return Obj
    */
    public function getSellsCurrentFy($business_id, $start, $end, $group_by_location = false)
    {
        $query = Transaction::leftjoin('transactions as SR', function ($join) {
            $join->on('SR.return_parent_id', '=', 'transactions.id')
                                    ->where('SR.type', 'sell_return');
        })
                            ->where('transactions.business_id', $business_id)
                            ->where('transactions.type', 'sell')
                            ->where('transactions.status', 'final')
                            ->whereBetween(DB::raw('date(transactions.transaction_date)'), [$start, $end]);

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }
        
        $query->groupBy(DB::raw("DATE_FORMAT(transactions.transaction_date, '%Y-%m')"))
        ->select(
            DB::raw("DATE_FORMAT(transactions.transaction_date, '%m-%Y') as yearmonth"),
            DB::raw("SUM( transactions.final_total - COALESCE(SR.final_total, 0)) as total_sells")
        );
        if ($group_by_location) {
            $query->addSelect('transactions.location_id');
            $query->groupBy('transactions.location_id');
        }

        $sells = $query->get();
        if (!$group_by_location) {
            $sells = $sells->pluck('total_sells', 'yearmonth');
        }
        
        return $sells;
    }

    /**
     * Retrives expense report
     *
     * @param int $business_id
     * @param array $filters
     * @param string $type = by_category (by_category or total)
     *
     * @return Obj
     */
    public function getExpenseReport(
        $business_id,
        $filters = [],
        $type = 'by_category'
    ) {
        $query = Transaction::leftjoin('expense_categories AS ec', 'transactions.expense_category_id', '=', 'ec.id')
                            ->where('transactions.business_id', $business_id)
                            ->whereIn('type', ['expense', 'expense_refund']);
        // ->where('payment_status', 'paid');

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($filters['location_id'])) {
            $query->where('transactions.location_id', $filters['location_id']);
        }

        if (!empty($filters['expense_for'])) {
            $query->where('transactions.expense_for', $filters['expense_for']);
        }

        if (!empty($filters['category'])) {
            $query->where('ec.id', $filters['category']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween(DB::raw('date(transaction_date)'), [$filters['start_date'],
                $filters['end_date']]);
        }

        //Check tht type of report and return data accordingly
        if ($type == 'by_category') {
            $expenses = $query->select(
                DB::raw("SUM( IF(transactions.type='expense_refund', -1 * final_total, final_total) ) as total_expense"),
                'ec.name as category'
            )
                        ->groupBy('expense_category_id')
                        ->get();
        } elseif ($type == 'total') {
            $expenses = $query->select(
                DB::raw("SUM( IF(transactions.type='expense_refund', -1 * final_total, final_total) ) as total_expense")
            )
                        ->first();
        }
        
        return $expenses;
    }

    /**
     * Get total paid amount for a transaction
     *
     * @param int $transaction_id
     *
     * @return int
     */
    public function getTotalPaid($transaction_id)
    {
        $total_paid = TransactionPayment::where('transaction_id', $transaction_id)
                ->select(DB::raw('SUM(IF( is_return = 0, amount, amount*-1))as total_paid'))
                ->first()
                ->total_paid;

        return $total_paid;
    }

    /**
     * Calculates the payment status and returns back.
     *
     * @param int $transaction_id
     * @param float $final_amount = null
     *
     * @return string
     */
    public function calculatePaymentStatus($transaction_id, $final_amount = null)
    {
        $total_paid = $this->getTotalPaid($transaction_id);

        if (is_null($final_amount)) {
            $final_amount = Transaction::find($transaction_id)->final_total;
        }

        $status = 'due';
        if ($final_amount <= $total_paid) {
            $status = 'paid';
        } elseif ($total_paid > 0 && $final_amount > $total_paid) {
            $status = 'partial';
        }

        return $status;
    }

    /**
     * Update the payment status for purchase or sell transactions. Returns
     * the status
     *
     * @param int $transaction_id
     *
     * @return string
     */
    public function updatePaymentStatus($transaction_id, $final_amount = null)
    {
        $status = $this->calculatePaymentStatus($transaction_id, $final_amount);
        Transaction::where('id', $transaction_id)
            ->update(['payment_status' => $status]);

        return $status;
    }

    /**
     * Purchase currency details
     *
     * @param int $business_id
     *
     * @return object
     */
    public function purchaseCurrencyDetails($business_id)
    {
        $business = Business::find($business_id);
        $output = ['purchase_in_diff_currency' => false,
                    'p_exchange_rate' => 1,
                    'decimal_seperator' => '.',
                    'thousand_seperator' => ',',
                    'symbol' => '',
                ];

        //Check if diff currency is used or not.
        if ($business->purchase_in_diff_currency == 1) {
            $output['purchase_in_diff_currency'] = true;
            $output['p_exchange_rate'] = $business->p_exchange_rate;

            $currency_id = $business->purchase_currency_id;
        } else {
            $output['purchase_in_diff_currency'] = false;
            $output['p_exchange_rate'] = 1;
            $currency_id = $business->currency_id;
        }

        $currency = Currency::find($currency_id);
        $output['thousand_separator'] = $currency->thousand_separator;
        $output['decimal_separator'] = $currency->decimal_separator;
        $output['symbol'] = $currency->symbol;
        $output['code'] = $currency->code;
        $output['name'] = $currency->currency;

        return (object)$output;
    }

    /**
     * Pay contact due at once
     *
     * @param obj $parent_payment, string $type
     *
     * @return void
     */
    public function payAtOnce($parent_payment, $type)
    {

        //Get all unpaid transaction for the contact
        $types = ['opening_balance', $type];
        
        if ($type == 'purchase_return') {
            $types = [$type];
        }

        $due_transactions = Transaction::where('contact_id', $parent_payment->payment_for)
                                ->whereIn('type', $types)
                                ->where('payment_status', '!=', 'paid')
                                ->orderBy('transaction_date', 'asc')
                                ->get();

        $total_amount = $parent_payment->amount;

        $tranaction_payments = [];
        if ($due_transactions->count()) {
            foreach ($due_transactions as $transaction) {
                $transaction_before = $transaction->replicate();
                //If sell check status is final
                if ($transaction->type == 'sell' && $transaction->status != 'final') {
                    continue;
                }
                if ($total_amount > 0) {
                    $total_paid = $this->getTotalPaid($transaction->id);
                    $due = $transaction->final_total - $total_paid;

                    $now = \Carbon::now()->toDateTimeString();

                    $array = [
                            'transaction_id' => $transaction->id,
                            'business_id' => $parent_payment->business_id,
                            'method' => $parent_payment->method,
                            'transaction_no' => $parent_payment->method,
                            'card_transaction_number' => $parent_payment->card_transaction_number,
                            'card_number' => $parent_payment->card_number,
                            'card_type' => $parent_payment->card_type,
                            'card_holder_name' => $parent_payment->card_holder_name,
                            'card_month' => $parent_payment->card_month,
                            'card_year' => $parent_payment->card_year,
                            'card_security' => $parent_payment->card_security,
                            'cheque_number' => $parent_payment->cheque_number,
                            'bank_account_number' => $parent_payment->bank_account_number,
                            'paid_on' => $parent_payment->paid_on,
                            'created_by' => $parent_payment->created_by,
                            'payment_for' => $parent_payment->payment_for,
                            'parent_id' => $parent_payment->id,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];

                    $prefix_type = 'purchase_payment';
                    if (in_array($transaction->type, ['sell', 'sell_return'])) {
                        $prefix_type = 'sell_payment';
                    }
                    $ref_count = $this->setAndGetReferenceCount($prefix_type, $parent_payment->business_id);
                    //Generate reference number
                    $payment_ref_no = $this->generateReferenceNumber($prefix_type, $ref_count, $parent_payment->business_id);
                    $array['payment_ref_no'] = $payment_ref_no;

                    if ($due <= $total_amount) {
                        $array['amount'] = $due;
                        $tranaction_payments[] = $array;

                        //Update transaction status to paid
                        $transaction->payment_status = 'paid';
                        $transaction->save();

                        $total_amount = $total_amount - $due;

                        $this->activityLog($transaction, 'payment_edited', $transaction_before);
                    } else {
                        $array['amount'] = $total_amount;
                        $tranaction_payments[] = $array;

                        //Update transaction status to partial
                        $transaction->payment_status = 'partial';
                        $transaction->save();
                        $total_amount = 0;
                        $this->activityLog($transaction, 'payment_edited', $transaction_before);
                        
                        break;
                    }
                }
            }

            //Insert new transaction payments
            if (!empty($tranaction_payments)) {
                TransactionPayment::insert($tranaction_payments);
            }
        }
        return $total_amount;
    }

    /**
     * Add a mapping between purchase & sell lines.
     * NOTE: Don't use request variable here, request variable don't exist while adding
     * dummybusiness via command line
     *
     * @param array $business
     * @param array $transaction_lines
     * @param string $mapping_type = purchase (purchase or stock_adjustment)
     * @param boolean $check_expiry = true
     * @param int $purchase_line_id (default: null)
     *
     * @return object
     */
    public function mapPurchaseSell($business, $transaction_lines, $mapping_type = 'purchase', $check_expiry = true, $purchase_line_id = null)
    {
        if (empty($transaction_lines)) {
            return false;
        }

        if (!empty($business['pos_settings']) && !is_array($business['pos_settings'])) {
            $business['pos_settings'] = json_decode($business['pos_settings'], true);
        }
        $allow_overselling = !empty($business['pos_settings']['allow_overselling']) ?
                            true : false;
                            
        //Set flag to check for expired items during SELLING only.
        $stop_selling_expired = false;
        if ($check_expiry) {
            if (session()->has('business') && request()->session()->get('business')['enable_product_expiry'] == 1 && request()->session()->get('business')['on_product_expiry'] == 'stop_selling') {
                if ($mapping_type == 'purchase') {
                    $stop_selling_expired = true;
                }
            }
        }

        $qty_selling = null;
        foreach ($transaction_lines as $line) {
            //Check if stock is not enabled then no need to assign purchase & sell
            $product = Product::find($line->product_id);
            if ($product->enable_stock != 1) {
                continue;
            }

            $qty_sum_query = $this->get_pl_quantity_sum_string('PL');
            
            //Get purchase lines, only for products with enable stock.
            $query = Transaction::join('purchase_lines AS PL', 'transactions.id', '=', 'PL.transaction_id')
                ->where('transactions.business_id', $business['id'])
                ->where('transactions.location_id', $business['location_id'])
                ->whereIn('transactions.type', ['purchase', 'purchase_transfer',
                    'opening_stock', 'production_purchase'])
                ->where('transactions.status', 'received')
                ->whereRaw("( $qty_sum_query ) < PL.quantity")
                ->where('PL.product_id', $line->product_id)
                ->where('PL.variation_id', $line->variation_id);

            //If product expiry is enabled then check for on expiry conditions
            if ($stop_selling_expired && empty($purchase_line_id)) {
                $stop_before = request()->session()->get('business')['stop_selling_before'];
                $expiry_date = \Carbon::today()->addDays($stop_before)->toDateString();
                $query->where( function($q) use($expiry_date) {
                    $q->whereNull('PL.exp_date')
                        ->orWhereRaw('PL.exp_date > ?', [$expiry_date]);
                });
            }
            
            //If lot number present consider only lot number purchase line
            if (!empty($line->lot_no_line_id)) {
                $query->where('PL.id', $line->lot_no_line_id);
            }

            //If purchase_line_id is given consider only that purchase line
            if (!empty($purchase_line_id)) {
                $query->where('PL.id', $purchase_line_id);
            }

            //Sort according to LIFO or FIFO
            if ($business['accounting_method'] == 'lifo') {
                $query = $query->orderBy('transaction_date', 'desc');
            } else {
                $query = $query->orderBy('transaction_date', 'asc');
            }

            $rows = $query->select(
                'PL.id as purchase_lines_id',
                DB::raw("(PL.quantity - ( $qty_sum_query )) AS quantity_available"),
                'PL.quantity_sold as quantity_sold',
                'PL.quantity_adjusted as quantity_adjusted',
                'PL.quantity_returned as quantity_returned',
                'PL.mfg_quantity_used as mfg_quantity_used',
                'transactions.invoice_no'
                    )->get();

            $purchase_sell_map = [];

            //Iterate over the rows, assign the purchase line to sell lines.
            $qty_selling = $line->quantity;
            foreach ($rows as $k => $row) {
                $qty_allocated = 0;

                //Check if qty_available is more or equal
                if ($qty_selling <= $row->quantity_available) {
                    $qty_allocated = $qty_selling;
                    $qty_selling = 0;
                } else {
                    $qty_selling = $qty_selling - $row->quantity_available;
                    $qty_allocated = $row->quantity_available;
                }

                //Check for sell mapping or stock adjsutment mapping
                if ($mapping_type == 'stock_adjustment') {
                    //Mapping of stock adjustment
                    if ($qty_allocated != 0) {
                        $purchase_adjustment_map[] =
                            ['stock_adjustment_line_id' => $line->id,
                                'purchase_line_id' => $row->purchase_lines_id,
                                'quantity' => $qty_allocated,
                                'created_at' => \Carbon::now(),
                                'updated_at' => \Carbon::now()
                            ];

                        //Update purchase line
                        PurchaseLine::where('id', $row->purchase_lines_id)
                            ->update(['quantity_adjusted' => $row->quantity_adjusted + $qty_allocated]);
                    }
                } elseif ($mapping_type == 'purchase') {
                    //Mapping of purchase
                    if ($qty_allocated != 0) {
                        $purchase_sell_map[] = ['sell_line_id' => $line->id,
                                'purchase_line_id' => $row->purchase_lines_id,
                                'quantity' => $qty_allocated,
                                'created_at' => \Carbon::now(),
                                'updated_at' => \Carbon::now()
                            ];

                        //Update purchase line
                        PurchaseLine::where('id', $row->purchase_lines_id)
                            ->update(['quantity_sold' => $row->quantity_sold + $qty_allocated]);
                    }
                } elseif ($mapping_type == 'production_purchase') {
                    //Mapping of purchase
                    if ($qty_allocated != 0) {
                        $purchase_sell_map[] = ['sell_line_id' => $line->id,
                                'purchase_line_id' => $row->purchase_lines_id,
                                'quantity' => $qty_allocated,
                                'created_at' => \Carbon::now(),
                                'updated_at' => \Carbon::now()
                            ];

                        //Update purchase line
                        PurchaseLine::where('id', $row->purchase_lines_id)
                            ->update(['mfg_quantity_used' => $row->mfg_quantity_used + $qty_allocated]);
                    }
                }

                if ($qty_selling == 0) {
                    break;
                }
            }

            if (! ($qty_selling == 0 || is_null($qty_selling))) {
                //If overselling not allowed through exception else create mapping with blank purchase_line_id
                if (!$allow_overselling) {
                    $variation = Variation::find($line->variation_id);
                    $mismatch_name = $product->name;
                    if (!empty($variation->sub_sku)) {
                        $mismatch_name .= ' ' . 'SKU: ' . $variation->sub_sku;
                    }
                    if (!empty($qty_selling)) {
                        $mismatch_name .= ' ' . 'Quantity: ' . abs($qty_selling);
                    }
                    
                    if ($mapping_type == 'purchase') {
                        $mismatch_error = trans(
                            "messages.purchase_sell_mismatch_exception",
                            ['product' => $mismatch_name]
                        );

                        if ($stop_selling_expired) {
                            $mismatch_error .= __('lang_v1.available_stock_expired');
                        }
                    } elseif ($mapping_type == 'stock_adjustment') {
                        $mismatch_error = trans(
                            "messages.purchase_stock_adjustment_mismatch_exception",
                            ['product' => $mismatch_name]
                        );
                    } else {
                        $mismatch_error = trans(
                            "lang_v1.quantity_mismatch_exception",
                            ['product' => $mismatch_name]
                        );
                    }

                    $business_name = optional(Business::find($business['id']))->name;
                    $location_name = optional(BusinessLocation::find($business['location_id']))->name;
                    \Log::emergency($mismatch_error . ' Business: ' . $business_name . ' Location: ' . $location_name);
                    throw new PurchaseSellMismatch($mismatch_error);
                } else {
                    //Mapping with no purchase line
                    $purchase_sell_map[] = ['sell_line_id' => $line->id,
                            'purchase_line_id' => 0,
                            'quantity' => $qty_selling,
                            'created_at' => \Carbon::now(),
                            'updated_at' => \Carbon::now()
                        ];
                }
            }

            //Insert the mapping
            if (!empty($purchase_adjustment_map)) {
                TransactionSellLinesPurchaseLines::insert($purchase_adjustment_map);
            }
            if (!empty($purchase_sell_map)) {
                TransactionSellLinesPurchaseLines::insert($purchase_sell_map);
            }
        }
    }

    /**
     * F => D (Delete all mapping lines, decrease the qty sold.)
     * D => F (Call the mapPurchaseSell function)
     * F => F (Check for quantity of existing product, call mapPurchase for new products.)
     *
     * @param  string $status_before
     * @param  object $transaction
     * @param  array $business
     * @param  array $deleted_line_ids = [] //deleted sell lines ids.
     *
     * @return void
     */
    public function adjustMappingPurchaseSell(
        $status_before,
        $transaction,
        $business,
        $deleted_line_ids = []
    ) {
        if ($status_before == 'final' && $transaction->status == 'draft') {
            //Get sell lines used for the transaction.
            $sell_purchases = Transaction::join('transaction_sell_lines AS SL', 'transactions.id', '=', 'SL.transaction_id')
                    ->join('transaction_sell_lines_purchase_lines as TSP', 'SL.id', '=', 'TSP.sell_line_id')
                    ->where('transactions.id', $transaction->id)
                    ->select('TSP.purchase_line_id', 'TSP.quantity', 'TSP.id')
                    ->get()
                    ->toArray();

            //Included the deleted sell lines
            if (!empty($deleted_line_ids)) {
                $deleted_sell_purchases = TransactionSellLinesPurchaseLines::whereIn('sell_line_id', $deleted_line_ids)
                            ->select('purchase_line_id', 'quantity', 'id')
                            ->get()
                            ->toArray();

                $sell_purchases = $sell_purchases + $deleted_sell_purchases;
            }

            //TODO: Optimize the query to take our of loop.
            $sell_purchase_ids = [];
            if (!empty($sell_purchases)) {
                //Decrease the quantity sold of products
                foreach ($sell_purchases as $row) {
                    PurchaseLine::where('id', $row['purchase_line_id'])
                        ->decrement('quantity_sold', $row['quantity']);

                    $sell_purchase_ids[] = $row['id'];
                }

                //Delete the lines.
                TransactionSellLinesPurchaseLines::whereIn('id', $sell_purchase_ids)
                    ->delete();
            }
        } elseif ($status_before == 'draft' && $transaction->status == 'final') {
            $this->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');
        } elseif ($status_before == 'final' && $transaction->status == 'final') {
            //Handle deleted line
            if (!empty($deleted_line_ids)) {
                $deleted_sell_purchases = TransactionSellLinesPurchaseLines::whereIn('sell_line_id', $deleted_line_ids)
                            ->select('sell_line_id', 'quantity')
                            ->get();
                if (!empty($deleted_sell_purchases)) {
                    foreach ($deleted_sell_purchases as $value) {
                        $this->mapDecrementPurchaseQuantity($value->sell_line_id, $value->quantity);
                    }
                }
            }

            //Check for update quantity, new added rows, deleted rows.
            $sell_purchases = Transaction::join('transaction_sell_lines AS SL', 'transactions.id', '=', 'SL.transaction_id')
                    ->leftjoin('transaction_sell_lines_purchase_lines as TSP', 'SL.id', '=', 'TSP.sell_line_id')
                    ->where('transactions.id', $transaction->id)
                    ->select(
                        'TSP.id as slpl_id',
                        'TSP.purchase_line_id',
                        'TSP.quantity AS tsp_quantity',
                        'TSP.id as tsp_id',
                        'SL.*'
                    )
                    ->get();

            $deleted_sell_lines = [];
            $new_sell_lines = [];
            $processed_sell_lines = [];

            foreach ($sell_purchases as $line) {
                if (empty($line->slpl_id)) {
                    $new_sell_lines[] = $line;
                } else {
                    //Skip if already processed.
                    if (in_array($line->slpl_id, $processed_sell_lines)) {
                        continue;
                    }
                    $processed_sell_lines[] = $line->slpl_id;

                    $total_sold_entry = TransactionSellLinesPurchaseLines::where('sell_line_id', $line->id)
                        ->select(DB::raw('SUM(quantity) AS quantity'))
                        ->first();

                    if ($total_sold_entry->quantity != $line->quantity) {
                        if ($line->quantity > $total_sold_entry->quantity) {
                            //If quantity is increased add it to new sell lines by decreasing tsp_quantity
                            $line_temp = $line;
                            $line_temp->quantity = $line_temp->quantity - $total_sold_entry->quantity;
                            $new_sell_lines[] = $line_temp;
                        } elseif ($line->quantity < $total_sold_entry->quantity) {
                            $decrement_qty = $total_sold_entry->quantity - $line->quantity;

                            $this->mapDecrementPurchaseQuantity($line->id, $decrement_qty);
                        }
                    }
                }
            }

            //Add mapping for new sell lines and for incremented quantity
            if (!empty($new_sell_lines)) {
                $this->mapPurchaseSell($business, $new_sell_lines);
            }
        }
    }

    /**
     * Decrease the purchase quantity from
     * transaction_sell_lines_purchase_lines and purchase_lines.quantity_sold
     *
     * @param  int $sell_line_id
     * @param  int $decrement_qty
     *
     * @return void
     */
    private function mapDecrementPurchaseQuantity($sell_line_id, $decrement_qty)
    {
        $sell_purchase_line = TransactionSellLinesPurchaseLines::
                                where('sell_line_id', $sell_line_id)
                                ->orderBy('id', 'desc')
                                ->get();

        foreach ($sell_purchase_line as $row) {
            if ($row->quantity > $decrement_qty) {
                PurchaseLine::where('id', $row->purchase_line_id)
                    ->decrement('quantity_sold', $decrement_qty);

                $row->quantity = $row->quantity - $decrement_qty;
                $row->save();
                $decrement_qty = 0;
            } else {
                PurchaseLine::where('id', $row->purchase_line_id)
                    ->decrement('quantity_sold', $decrement_qty);
                $row->delete();
            }

            $decrement_qty = $decrement_qty - $row->quantity;
            if ($decrement_qty <= 0) {
                break;
            }
        }
    }

    /**
     * Decrement quantity adjusted in product line according to
     * transaction_sell_lines_purchase_lines
     * Used in delete of stock adjustment
     *
     * @param  array $line_ids
     *
     * @return boolean
     */
    public function mapPurchaseQuantityForDeleteStockAdjustment($line_ids)
    {
        if (empty($line_ids)) {
            return true;
        }

        $map_line = TransactionSellLinesPurchaseLines::whereIn('stock_adjustment_line_id', $line_ids)
                            ->orderBy('id', 'desc')
                            ->get();

        foreach ($map_line as $row) {
            PurchaseLine::where('id', $row->purchase_line_id)
                ->decrement('quantity_adjusted', $row->quantity);
        }

        //Delete the tslp line.
        TransactionSellLinesPurchaseLines::whereIn('stock_adjustment_line_id', $line_ids)
            ->delete();

        return true;
    }

    /**
     * Adjust the existing mapping between purchase & sell on edit of
     * purchase
     *
     * @param  string $before_status
     * @param  object $transaction
     * @param  object $delete_purchase_lines
     *
     * @return void
     */
    public function adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines)
    {
        if ($before_status == 'received' && $transaction->status == 'received') {
            //Check if there is some irregularities between purchase & sell and make appropiate adjustment.

            //Get all purchase line having irregularities.
            $purchase_lines = Transaction::join(
                'purchase_lines AS PL',
                'transactions.id',
                '=',
                'PL.transaction_id'
            )
                    ->join(
                        'transaction_sell_lines_purchase_lines AS TSPL',
                        'PL.id',
                        '=',
                        'TSPL.purchase_line_id'
                    )
                    ->groupBy('TSPL.purchase_line_id')
                    ->where('transactions.id', $transaction->id)
                    ->havingRaw('SUM(TSPL.quantity) > MAX(PL.quantity)')
                    ->select(['TSPL.purchase_line_id AS id',
                            DB::raw('SUM(TSPL.quantity) AS tspl_quantity'),
                            DB::raw('MAX(PL.quantity) AS pl_quantity')
                        ])
                    ->get()
                    ->toArray();
        } elseif ($before_status == 'received' && $transaction->status != 'received') {
            //Delete sell for those & add new sell or throw error.
            $purchase_lines = Transaction::join(
                'purchase_lines AS PL',
                'transactions.id',
                '=',
                'PL.transaction_id'
            )
                    ->join(
                        'transaction_sell_lines_purchase_lines AS TSPL',
                        'PL.id',
                        '=',
                        'TSPL.purchase_line_id'
                    )
                    ->groupBy('TSPL.purchase_line_id')
                    ->where('transactions.id', $transaction->id)
                    ->select(['TSPL.purchase_line_id AS id',
                        DB::raw('MAX(PL.quantity) AS pl_quantity')
                    ])
                    ->get()
                    ->toArray();
        } else {
            return true;
        }

        //Get detail of purchase lines deleted
        if (!empty($delete_purchase_lines)) {
            $purchase_lines = $delete_purchase_lines->toArray() + $purchase_lines;
        }

        //All sell lines & Stock adjustment lines.
        $sell_lines = [];
        $stock_adjustment_lines = [];
        foreach ($purchase_lines as $purchase_line) {
            $tspl_quantity = isset($purchase_line['tspl_quantity']) ? $purchase_line['tspl_quantity'] : 0;
            $pl_quantity = isset($purchase_line['pl_quantity']) ? $purchase_line['pl_quantity'] : $purchase_line['quantity'];


            $extra_sold = abs($tspl_quantity - $pl_quantity);

            //Decrease the quantity from transaction_sell_lines_purchase_lines or delete it if zero
            $tspl = TransactionSellLinesPurchaseLines::where('purchase_line_id', $purchase_line['id'])
                ->leftjoin(
                    'transaction_sell_lines AS SL',
                    'transaction_sell_lines_purchase_lines.sell_line_id',
                    '=',
                    'SL.id'
                )
                ->leftjoin(
                    'stock_adjustment_lines AS SAL',
                    'transaction_sell_lines_purchase_lines.stock_adjustment_line_id',
                    '=',
                    'SAL.id'
                )
                ->orderBy('transaction_sell_lines_purchase_lines.id', 'desc')
                ->select(['SL.product_id AS sell_product_id',
                        'SL.variation_id AS sell_variation_id',
                        'SL.id AS sell_line_id',
                        'SAL.product_id AS adjust_product_id',
                        'SAL.variation_id AS adjust_variation_id',
                        'SAL.id AS adjust_line_id',
                        'transaction_sell_lines_purchase_lines.quantity',
                        'transaction_sell_lines_purchase_lines.purchase_line_id', 'transaction_sell_lines_purchase_lines.id as tslpl_id'])
                ->get();

            foreach ($tspl as $row) {
                if ($row->quantity <= $extra_sold) {
                    if (!empty($row->sell_line_id)) {
                        $sell_lines[] = (object)['id' => $row->sell_line_id,
                                'quantity' => $row->quantity,
                                'product_id' => $row->sell_product_id,
                                'variation_id' => $row->sell_variation_id,
                            ];
                        PurchaseLine::where('id', $row->purchase_line_id)
                            ->decrement('quantity_sold', $row->quantity);
                    } else {
                        $stock_adjustment_lines[] =
                            (object)['id' => $row->adjust_line_id,
                                'quantity' => $row->quantity,
                                'product_id' => $row->adjust_product_id,
                                'variation_id' => $row->adjust_variation_id,
                            ];
                        PurchaseLine::where('id', $row->purchase_line_id)
                            ->decrement('quantity_adjusted', $row->quantity);
                    }

                    $extra_sold = $extra_sold - $row->quantity;
                    TransactionSellLinesPurchaseLines::where('id', $row->tslpl_id)->delete();
                } else {
                    if (!empty($row->sell_line_id)) {
                        $sell_lines[] = (object)['id' => $row->sell_line_id,
                                'quantity' => $extra_sold,
                                'product_id' => $row->sell_product_id,
                                'variation_id' => $row->sell_variation_id,
                            ];
                        PurchaseLine::where('id', $row->purchase_line_id)
                            ->decrement('quantity_sold', $extra_sold);
                    } else {
                        $stock_adjustment_lines[] =
                            (object)['id' => $row->adjust_line_id,
                                'quantity' => $extra_sold,
                                'product_id' => $row->adjust_product_id,
                                'variation_id' => $row->adjust_variation_id,
                            ];

                        PurchaseLine::where('id', $row->purchase_line_id)
                            ->decrement('quantity_adjusted', $extra_sold);
                    }

                    TransactionSellLinesPurchaseLines::where('id', $row->tslpl_id)->update(['quantity' => $row->quantity - $extra_sold]);
                    
                    $extra_sold = 0;
                }

                if ($extra_sold == 0) {
                    break;
                }
            }
        }

        $business = Business::find($transaction->business_id)->toArray();
        $business['location_id'] = $transaction->location_id;

        //Allocate the sold lines to purchases.
        if (!empty($sell_lines)) {
            $sell_lines = (object)$sell_lines;
            $this->mapPurchaseSell($business, $sell_lines, 'purchase');
        }

        //Allocate the stock adjustment lines to purchases.
        if (!empty($stock_adjustment_lines)) {
            $stock_adjustment_lines = (object)$stock_adjustment_lines;
            $this->mapPurchaseSell($business, $stock_adjustment_lines, 'stock_adjustment');
        }
    }

    /**
     * Check if transaction can be edited based on business     transaction_edit_days
     *
     * @param  int/object $transaction
     * @param  int $edit_duration
     *
     * @return boolean
     */
    public function canBeEdited($transaction, $edit_duration)
    {
        if (!is_object($transaction)) {
            $transaction = Transaction::find($transaction);
        }
        if (empty($transaction)) {
            return false;
        }

        $date = \Carbon::parse($transaction->transaction_date)
                    ->addDays($edit_duration);

        $today = today();

        if ($date->gte($today)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Calculates total stock on the given date
     *
     * @param int $business_id
     * @param string $date
     * @param int $location_id
     * @param boolean $is_opening = false
     *
     * @return float
     */
    public function getOpeningClosingStock($business_id, $date, $location_id, $is_opening = false, $by_sale_price = false, $filters = [])
    {
        $query = PurchaseLine::join(
            'transactions as purchase',
            'purchase_lines.transaction_id',
            '=',
            'purchase.id'
        )
        ->where('purchase.type', '!=', 'purchase_order')
        ->where('purchase.business_id', $business_id);

        $price_query_part = "(purchase_lines.purchase_price + 
                            COALESCE(purchase_lines.item_tax, 0))";

        if ($by_sale_price) {
            $price_query_part = 'v.sell_price_inc_tax';
        } 

        $query->leftjoin('variations as v', 'v.id', '=', 'purchase_lines.variation_id')
                ->leftjoin('products as p', 'p.id', '=', 'purchase_lines.product_id');

        if (!empty($filters['category_id'])) {
            $query->where('p.category_id',$filters['category_id']);
        }
        if (!empty($filters['sub_category_id'])) {
            $query->where('p.sub_category_id',$filters['sub_category_id']);
        }
        if (!empty($filters['brand_id'])) {
            $query->where('p.brand_id',$filters['brand_id']);
        }
        if (!empty($filters['unit_id'])) {
            $query->where('p.unit_id',$filters['unit_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('purchase.created_by',$filters['user_id']);
        }

        //If opening
        if ($is_opening) {
            $next_day = \Carbon::createFromFormat('Y-m-d', $date)->addDay()->format('Y-m-d');
            
            $query->where(function ($query) use ($date, $next_day) {
                $query->whereRaw("date(transaction_date) <= '$date'")
                    ->orWhereRaw("date(transaction_date) = '$next_day' AND purchase.type='opening_stock' ");
            });
        } else {
            $query->whereRaw("date(transaction_date) <= '$date'");
        }
                    
        $query->select(
            DB::raw("SUM((purchase_lines.quantity - purchase_lines.quantity_returned - purchase_lines.quantity_adjusted -
                            (SELECT COALESCE(SUM(tspl.quantity - tspl.qty_returned), 0) FROM 
                            transaction_sell_lines_purchase_lines AS tspl
                            JOIN transaction_sell_lines as tsl ON 
                            tspl.sell_line_id=tsl.id 
                            JOIN transactions as sale ON 
                            tsl.transaction_id=sale.id 
                            WHERE tspl.purchase_line_id = purchase_lines.id AND 
                            date(sale.transaction_date) <= '$date') ) * $price_query_part
                        ) as stock")
        );

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('purchase.location_id', $permitted_locations);
        }

        if (!empty($location_id)) {
            $query->where('purchase.location_id', $location_id);
        }

        $details = $query->first();
        return $details->stock;
    }

    /**
     * Gives the total sell commission for a commission agent within the date range passed
     *
     * @param int $business_id
     * @param string $start_date
     * @param string $end_date
     * @param int $location_id
     * @param int $commission_agent
     *
     * @return array
     */
    public function getTotalSellCommission($business_id, $start_date = null, $end_date = null, $location_id = null, $commission_agent = null)
    {
        //Query to sum total sell without line tax and order tax
        $query = TransactionSellLine::leftjoin('transactions as t', 'transaction_sell_lines.transaction_id', '=', 't.id')
                            ->where('t.business_id', $business_id)
                            ->where('t.type', 'sell')
                            ->where('t.status', 'final')
                            ->select(DB::raw("SUM( (transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price ) as final_total"));

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(t.transaction_date)'), [$start_date, $end_date]);
        }

        //Filter by the location
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        if (!empty($commission_agent)) {
            $query->where('t.commission_agent', $commission_agent);
        }

        $sell_details = $query->get();

        $output['total_sales_with_commission'] = $sell_details->sum('final_total');

        return $output;
    }

    public function getTotalPaymentWithCommission($business_id, $start_date = null, $end_date = null, $location_id = null, $commission_agent = null)
    {
        $query = TransactionPayment::join('transactions as t', 
            'transaction_payments.transaction_id', '=', 't.id')
                            ->where('t.business_id', $business_id)
                            ->where('t.type', 'sell')
                            ->where('t.status', 'final')
                            ->select(DB::raw('SUM(IF( is_return = 0, amount, amount*-1)) as total_paid'));

        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
        }

        //Filter by the location
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        if (!empty($commission_agent)) {
            $query->where('t.commission_agent', $commission_agent);
        }

        $payment_details = $query->first();

        $output['total_payment_with_commission'] = $payment_details->total_paid;

        return $output;
    }

    /**
     * Add Sell transaction
     *
     * @param int $business_id
     * @param array $input
     * @param float $invoice_total
     * @param int $user_id
     *
     * @return boolean
     */
    public function createSellReturnTransaction($business_id, $input, $invoice_total, $user_id)
    {
        $transaction = Transaction::create([
            'business_id' => $business_id,
            'location_id' => $input['location_id'],
            'type' => 'sell_return',
            'status' => 'final',
            'contact_id' => $input['contact_id'],
            'customer_group_id' => $input['customer_group_id'],
            'ref_no' => $input['ref_no'],
            'total_before_tax' => $invoice_total['total_before_tax'],
            'transaction_date' => $input['transaction_date'],
            'tax_id' => null,
            'discount_type' => $input['discount_type'],
            'discount_amount' => $this->num_uf($input['discount_amount']),
            'tax_amount' => $invoice_total['tax'],
            'final_total' => $this->num_uf($input['final_total']),
            'additional_notes' => !empty($input['additional_notes']) ? $input['additional_notes'] : null,
            'created_by' => $user_id,
            'is_quotation' => isset($input['is_quotation']) ? $input['is_quotation'] : 0
        ]);

        return $transaction;
    }

    public function groupTaxDetails($tax, $amount)
    {
        if (!is_object($tax)) {
            $tax = TaxRate::find($tax);
        }

        if (!empty($tax)) {
            $sub_taxes = $tax->sub_taxes;

            $sum = $tax->sub_taxes->sum('amount');

            $details = [];
            foreach ($sub_taxes as $sub_tax) {
                $details[] = [
                        'id' => $sub_tax->id,
                        'name' => $sub_tax->name,
                        'amount' => $sub_tax->amount,
                        'calculated_tax' => ($amount / $sum) * $sub_tax->amount,
                    ];
            }

            return $details;
        } else {
            return [];
        }
    }

    public function sumGroupTaxDetails($group_tax_details)
    {
        $output = [];

        foreach ($group_tax_details as $group_tax_detail) {
            if (!isset($output[$group_tax_detail['name']])) {
                $output[$group_tax_detail['name']] = 0.00;
            }
            $output[$group_tax_detail['name']] += $group_tax_detail['calculated_tax'];
        }

        return $output;
    }

    /**
     * Retrieves all available lot numbers of a product from variation id
     *
     * @param  int $variation_id
     * @param  int $business_id
     * @param  int $location_id
     *
     * @return boolean
     */
    public function getLotNumbersFromVariation($variation_id, $business_id, $location_id, $exclude_empty_lot = false)
    {
        $query = PurchaseLine::join(
            'transactions as T',
            'purchase_lines.transaction_id',
            '=',
            'T.id'
        )
                                        ->where('T.business_id', $business_id)
                                        ->where('T.location_id', $location_id)
                                        ->where('purchase_lines.variation_id', $variation_id);

        //If expiry is disabled
        if (request()->session()->get('business.enable_product_expiry') == 0) {
            $query->whereNotNull('purchase_lines.lot_number');
        }
        if ($exclude_empty_lot) {
            $query->whereRaw('(purchase_lines.quantity_sold + purchase_lines.quantity_adjusted + purchase_lines.quantity_returned) < purchase_lines.quantity');
        } else {
            $query->whereRaw('(purchase_lines.quantity_sold + purchase_lines.quantity_adjusted + purchase_lines.quantity_returned) <= purchase_lines.quantity');
        }

        $purchase_lines = $query->select('purchase_lines.id as purchase_line_id', 'lot_number', 'purchase_lines.exp_date as exp_date', DB::raw('(purchase_lines.quantity - (purchase_lines.quantity_sold + purchase_lines.quantity_adjusted + purchase_lines.quantity_returned)) AS qty_available'))->get();
        return $purchase_lines;
    }

    /**
     * Checks if credit limit of a customer is exceeded
     *
     * @param  array $input
     * @param  int $exclude_transaction_id (For update sell)
     *
     * @return mixed
     * if exceeded returns credit_limit else false
     */
    public function isCustomerCreditLimitExeeded(
        $input,
        $exclude_transaction_id = null,
        $uf_number = true
    ) {
        //If draft ignore credit limit check
        if ($input['status'] == 'draft' || (isset($input['type']) && $input['type'] == 'sales_order')) {
            return false;
        }

        $final_total = $uf_number ? $this->num_uf($input['final_total']) : $input['final_total'];
        $curr_total_payment = 0;
        $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;
        if (!empty($input['payment']) && !$is_credit_sale) {
            foreach ($input['payment'] as $payment) {
                $curr_total_payment += $this->num_uf($payment['amount']);
            }
        }

        //If not credit sell ignore credit limit check
        if ($final_total <= $curr_total_payment) {
            return false;
        }

        $credit_limit = Contact::find($input['contact_id'])->credit_limit;

        if ($credit_limit == null) {
            return false;
        }

        $query = Contact::where('contacts.id', $input['contact_id'])
                ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id');

        //Exclude transaction id if update transaction
        if (!empty($exclude_transaction_id)) {
            $query->where('t.id', '!=', $exclude_transaction_id);
        }
                                    
        $credit_details =  $query->select(
            DB::raw("SUM(IF(t.type = 'sell', final_total, 0)) as total_invoice"),
            DB::raw("SUM(IF(t.type = 'sell', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_paid")
        )->first();

        $total_invoice = !empty($credit_details->total_invoice) ? $credit_details->total_invoice : 0;
        $invoice_paid = !empty($credit_details->invoice_paid) ? $credit_details->invoice_paid : 0;

        $curr_due = $final_total - $curr_total_payment;

        $total_due = $total_invoice - $invoice_paid + $curr_due;

        if ($total_due <= $credit_limit) {
            return false;
        }

        return $credit_limit;
    }


    /**
     * Creates a new opening balance transaction for a contact
     *
     * @param  int $business_id
     * @param  int $contact_id
     * @param  int $amount
     *
     * @return void
     */
    public function createOpeningBalanceTransaction($business_id, $contact_id, $amount, $created_by, $uf_data = true)
    {
        $business_location = BusinessLocation::where('business_id', $business_id)
                                                        ->first();
        $final_amount = $uf_data ? $this->num_uf($amount) : $amount;
        $ob_data = [
                    'business_id' => $business_id,
                    'location_id' => $business_location->id,
                    'type' => 'opening_balance',
                    'status' => 'final',
                    'payment_status' => 'due',
                    'contact_id' => $contact_id,
                    'transaction_date' => \Carbon::now(),
                    'total_before_tax' => $final_amount,
                    'final_total' => $final_amount,
                    'created_by' => $created_by
                ];
        //Update reference count
        $ob_ref_count = $this->setAndGetReferenceCount('opening_balance', $business_id);
        //Generate reference number
        $ob_data['ref_no'] = $this->generateReferenceNumber('opening_balance', $ob_ref_count, $business_id);
        //Create opening balance transaction
        Transaction::create($ob_data);
    }

    /**
     * Updates quantity sold in purchase line for sell return
     *
     * @param  obj $sell_line
     * @param  decimal $new_quantity
     * @param  decimal $old_quantity
     *
     * @return void
     */
    public function updateQuantitySoldFromSellLine($sell_line, $new_quantity, $old_quantity, $uf_number = true)
    {
        $new_quantity = $uf_number ? $this->num_uf($new_quantity) : $new_quantity;
        $old_quantity = $uf_number ? $this->num_uf($old_quantity) : $old_quantity;
        $qty_difference = $new_quantity - $old_quantity;

        if ($qty_difference != 0) {
            $qty_left_to_update = $qty_difference;
            $sell_line_purchase_lines = TransactionSellLinesPurchaseLines::where('sell_line_id', $sell_line->id)->get();
            
            //Return from each purchase line
            foreach ($sell_line_purchase_lines as $tslpl) {
                //If differnce is +ve decrease quantity sold
                if ($qty_difference > 0) {
                    if ($tslpl->qty_returned < $tslpl->quantity) {
                        //Quantity that can be returned from sell line purchase line
                        $tspl_qty_left_to_return = $tslpl->quantity - $tslpl->qty_returned;

                        $purchase_line = PurchaseLine::find($tslpl->purchase_line_id);
                        if ($qty_left_to_update <= $tspl_qty_left_to_return) {

                            if(!empty($purchase_line)){
                                $purchase_line->quantity_sold -= $qty_left_to_update;
                                $purchase_line->save();
                            }
                            

                            $tslpl->qty_returned += $qty_left_to_update;
                            $tslpl->save();
                            break;
                        } else {

                            if(!empty($purchase_line)){
                                $purchase_line->quantity_sold -= $tspl_qty_left_to_return;
                                $purchase_line->save();
                            }

                            $tslpl->qty_returned += $tspl_qty_left_to_return;
                            $tslpl->save();
                            $qty_left_to_update -= $tspl_qty_left_to_return;
                        }
                    }
                } //If differnce is -ve increase quantity sold
                elseif ($qty_difference < 0) {
                    $purchase_line = PurchaseLine::find($tslpl->purchase_line_id);

                    if (!empty($purchase_line)) {
                        $tspl_qty_to_return = $tslpl->qty_returned + $qty_left_to_update;
                        if ($tspl_qty_to_return >= 0) {
                            $purchase_line->quantity_sold -= $qty_left_to_update;
                            $purchase_line->save();

                            $tslpl->qty_returned += $qty_left_to_update;
                            $tslpl->save();
                            break;
                        } else {
                            $purchase_line->quantity_sold += $tslpl->quantity;
                            $purchase_line->save();

                            $tslpl->qty_returned = 0;
                            $tslpl->save();
                            $qty_left_to_update += $tslpl->quantity;
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if return exist for a particular purchase or sell
     * @param id $transacion_id
     *
     * @return boolean
     */
    public function isReturnExist($transacion_id)
    {
        return Transaction::where('return_parent_id', $transacion_id)->exists();
    }

    /**
    * Recalculates sell line data according to subunit data
    *
    * @param integer $unit_id
    *
    * @return array
    */
    public function recalculateSellLineTotals($business_id, $sell_line)
    {
        $unit_details = $this->getSubUnits($business_id, $sell_line->product->unit->id);

        $sub_unit = null;
        $sub_unit_id = $sell_line->sub_unit_id;
        foreach ($unit_details as $key => $value) {
            if ($key == $sub_unit_id) {
                $sub_unit = $value;
            }
        }

        if (!empty($sub_unit)) {
            $multiplier = !empty($sub_unit['multiplier']) ? $sub_unit['multiplier'] : 1;

            if ($sell_line->line_discount_type == 'fixed') {
                $sell_line->line_discount_amount = $sell_line->line_discount_amount * $multiplier;
            }
            $sell_line->orig_quantity = $sell_line->quantity;
            $sell_line->multiplier = $multiplier;
            $sell_line->quantity = $sell_line->quantity / $multiplier;
            $sell_line->unit_price_before_discount = $sell_line->unit_price_before_discount * $multiplier;
            $sell_line->unit_price = $sell_line->unit_price * $multiplier;
            $sell_line->unit_price_inc_tax = $sell_line->unit_price_inc_tax * $multiplier;
            $sell_line->item_tax = $sell_line->item_tax * $multiplier;
            $sell_line->quantity_returned = $sell_line->quantity_returned / $multiplier;

            $sell_line->unit_details = $unit_details;
        }

        return $sell_line;
    }

    /**
     * Check if lot number is used in any sell
     * @param obj $transaction
     *
     * @return boolean
     */
    public function isLotUsed($transaction)
    {
        foreach ($transaction->purchase_lines as $purchase_line) {
            $exists = TransactionSellLine::where('lot_no_line_id', $purchase_line->id)->exists();
            if ($exists) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates recurring invoice from existing sale
     * @param obj $transaction, bool $is_draft
     *
     * @return obj $recurring_invoice
     */
    public function createRecurringInvoice($transaction, $is_draft = false)
    {
        $data = $transaction->toArray();

        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        if ($is_draft) {
            $data['status'] = 'draft';
        }
        $data['payment_status'] = 'due';
        $data['recur_parent_id'] = $transaction->id;
        $data['is_recurring'] = 0;
        $data['recur_interval'] = null;
        $data['recur_interval_type'] = null;
        $data['recur_repetitions'] = 0;
        $data['recur_stopped_on'] = null;
        $data['transaction_date'] = \Carbon::now();

        if (isset($data['invoice_token'])) {
            $data['invoice_token'] = null;
        }

        if (isset($data['woocommerce_order_id'])) {
            $data['woocommerce_order_id'] = null;
        }

        if (isset($data['recurring_invoices'])) {
            unset($data['recurring_invoices']);
        }

        if (isset($data['sell_lines'])) {
            unset($data['sell_lines']);
        }

        if (isset($data['business'])) {
            unset($data['business']);
        }

        $data['invoice_no'] = $this->getInvoiceNumber($transaction->business_id, $data['status'], $data['location_id']);

        $recurring_invoice = Transaction::create($data);

        $recurring_sell_lines = [];

        foreach ($transaction->sell_lines as $sell_line) {
            $sell_line_data = $sell_line->toArray();

            unset($sell_line_data['id']);
            unset($sell_line_data['created_at']);
            unset($sell_line_data['updated_at']);
            unset($sell_line_data['product']);

            if (isset($sell_line_data['quantity_returned'])) {
                unset($sell_line_data['quantity_returned']);
            }
            if (isset($sell_line_data['lot_no_line_id'])) {
                unset($sell_line_data['lot_no_line_id']);
            }
            if (isset($sell_line_data['woocommerce_line_items_id'])) {
                unset($sell_line_data['woocommerce_line_items_id']);
            }
            $recurring_sell_lines[] = $sell_line_data;
        }

        $recurring_invoice->sell_lines()->createMany($recurring_sell_lines);

        return $recurring_invoice;
    }

    /**
     * Retrieves and sum total amount paid for a transaction
     * @param int $transaction_id
     *
     */
    public function getTotalAmountPaid($transaction_id)
    {
        $paid = TransactionPayment::where(
            'transaction_id',
            $transaction_id
        )->sum('amount');
        return $paid;
    }

    /**
     * Calculates transaction totals for the given transaction types
     *
     * @param  int $business_id
     * @param  array $transaction_types
     * available types = ['purchase_return', 'sell_return', 'expense',
     * 'stock_adjustment', 'sell_transfer', 'purchase', 'sell']
     * @param  string $start_date = null
     * @param  string $end_date = null
     * @param  int $location_id = null
     * @param  int $created_by = null
     *
     * @return array
     */
    public function getTransactionTotals(
        $business_id,
        $transaction_types,
        $start_date = null,
        $end_date = null,
        $location_id = null,
        $created_by = null
        ) {
        $query = Transaction::where('business_id', $business_id);
        
        //Check for permitted locations of a user
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('transactions.transaction_date', '>=', $start_date)
                ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if (empty($start_date) && !empty($end_date)) {
            $query->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        //Filter by the location
        if (!empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        //Filter by created_by
        if (!empty($created_by)) {
            $query->where('transactions.created_by', $created_by);
        }

        if (in_array('purchase_return', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='purchase_return', final_total, 0)) as total_purchase_return_inc_tax"),
                DB::raw("SUM(IF(transactions.type='purchase_return', total_before_tax, 0)) as total_purchase_return_exc_tax")
            );
        }

        if (in_array('sell_return', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='sell_return', final_total, 0)) as total_sell_return_inc_tax"),
                DB::raw("SUM(IF(transactions.type='sell_return', total_before_tax, 0)) as total_sell_return_exc_tax"),
                DB::raw("SUM(IF(transactions.type='sell_return', IF(discount_type = 'percentage', COALESCE(discount_amount, 0)*total_before_tax/100, COALESCE(discount_amount, 0)), 0)) as total_sell_return_discount")
            );
        }

        if (in_array('sell_transfer', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='sell_transfer', shipping_charges, 0)) as total_transfer_shipping_charges")
                
            );
        }

        if (in_array('expense', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='expense', final_total, 0)) as total_expense")
            );

            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='expense_refund', final_total, 0)) as total_expense_refund")
            );
        }

        if (in_array('payroll', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='payroll', final_total, 0)) as total_payroll")
            );
        }

        if (in_array('stock_adjustment', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='stock_adjustment', final_total, 0)) as total_adjustment"),
                DB::raw("SUM(IF(transactions.type='stock_adjustment', total_amount_recovered, 0)) as total_recovered")
            );
        }

        if (in_array('purchase', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='purchase', IF(discount_type = 'percentage', COALESCE(discount_amount, 0)*total_before_tax/100, COALESCE(discount_amount, 0)), 0)) as total_purchase_discount")
            );
        }

        if (in_array('sell', $transaction_types)) {
            $query->addSelect(
                DB::raw("SUM(IF(transactions.type='sell' AND transactions.status='final', IF(discount_type = 'percentage', COALESCE(discount_amount, 0)*total_before_tax/100, COALESCE(discount_amount, 0)), 0)) as total_sell_discount"),
                DB::raw("SUM(IF(transactions.type='sell' AND transactions.status='final', rp_redeemed_amount, 0)) as total_reward_amount"),
                DB::raw("SUM(IF(transactions.type='sell' AND transactions.status='final', round_off_amount, 0)) as total_sell_round_off")
            );
        }

        $transaction_totals = $query->first();
        $output = [];

        if (in_array('purchase_return', $transaction_types)) {
            $output['total_purchase_return_inc_tax'] = !empty($transaction_totals->total_purchase_return_inc_tax) ?
                $transaction_totals->total_purchase_return_inc_tax : 0;

            $output['total_purchase_return_exc_tax'] =
                !empty($transaction_totals->total_purchase_return_exc_tax) ?
                $transaction_totals->total_purchase_return_exc_tax : 0;
        }

        if (in_array('sell_return', $transaction_types)) {
            $output['total_sell_return_inc_tax'] =
                !empty($transaction_totals->total_sell_return_inc_tax) ?
                $transaction_totals->total_sell_return_inc_tax : 0;

            $output['total_sell_return_exc_tax'] =
                !empty($transaction_totals->total_sell_return_exc_tax) ?
                $transaction_totals->total_sell_return_exc_tax : 0;

            $output['total_sell_return_discount'] =
                !empty($transaction_totals->total_sell_return_discount) ?
                $transaction_totals->total_sell_return_discount : 0;
        }

        if (in_array('sell_transfer', $transaction_types)) {
            $output['total_transfer_shipping_charges'] =
                !empty($transaction_totals->total_transfer_shipping_charges) ?
                $transaction_totals->total_transfer_shipping_charges : 0;
        }

        if (in_array('expense', $transaction_types)) {
            $total_expense = !empty($transaction_totals->total_expense) ?
                $transaction_totals->total_expense : 0;
            $total_expense_refund = !empty($transaction_totals->total_expense_refund) ?
                $transaction_totals->total_expense_refund : 0;
            $output['total_expense'] = $total_expense - $total_expense_refund;
        }

        if (in_array('payroll', $transaction_types)) {
            $output['total_payroll'] =
                !empty($transaction_totals->total_payroll) ?
                $transaction_totals->total_payroll : 0;
        }

        if (in_array('stock_adjustment', $transaction_types)) {
            $output['total_adjustment'] =
                !empty($transaction_totals->total_adjustment) ?
                $transaction_totals->total_adjustment : 0;

            $output['total_recovered'] =
                !empty($transaction_totals->total_recovered) ?
                $transaction_totals->total_recovered : 0;
        }

        if (in_array('purchase', $transaction_types)) {
            $output['total_purchase_discount'] =
                !empty($transaction_totals->total_purchase_discount) ?
                $transaction_totals->total_purchase_discount : 0;
        }

        if (in_array('sell', $transaction_types)) {
            $output['total_sell_discount'] =
                !empty($transaction_totals->total_sell_discount) ?
                $transaction_totals->total_sell_discount : 0;

            $output['total_reward_amount'] =
                !empty($transaction_totals->total_reward_amount) ?
                $transaction_totals->total_reward_amount : 0;

            $output['total_sell_round_off'] =
                !empty($transaction_totals->total_sell_round_off) ?
                $transaction_totals->total_sell_round_off : 0;
        }
        
        return $output;
    }

    public function getGrossProfit($business_id, $start_date = null, $end_date = null, $location_id = null, $user_id = null)
    {
        $query = TransactionSellLine
            ::join('transactions as sale', 'transaction_sell_lines.transaction_id', '=', 'sale.id')
            ->leftjoin('transaction_sell_lines_purchase_lines as TSPL', 'transaction_sell_lines.id', '=', 'TSPL.sell_line_id')
            ->leftjoin(
                'purchase_lines as PL',
                'TSPL.purchase_line_id',
                '=',
                'PL.id'
            )
            ->where('sale.type', 'sell')
            ->where('sale.status', 'final')
            ->join('products as P', 'transaction_sell_lines.product_id', '=', 'P.id')
            ->where('sale.business_id', $business_id)
            ->where('transaction_sell_lines.children_type', '!=', 'combo');
        //If type combo: find childrens, sale price parent - get PP of childrens
        $query->select(DB::raw('SUM(IF (TSPL.id IS NULL AND P.type="combo", ( 
            SELECT Sum((tspl2.quantity - tspl2.qty_returned) * (tsl.unit_price_inc_tax - pl2.purchase_price_inc_tax)) AS total
                FROM transaction_sell_lines AS tsl
                    JOIN transaction_sell_lines_purchase_lines AS tspl2
                ON tsl.id=tspl2.sell_line_id 
                JOIN purchase_lines AS pl2 
                ON tspl2.purchase_line_id = pl2.id 
                WHERE tsl.parent_sell_line_id = transaction_sell_lines.id), IF(P.enable_stock=0,(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax,   
                (TSPL.quantity - TSPL.qty_returned) * (transaction_sell_lines.unit_price_inc_tax - PL.purchase_price_inc_tax)) )) AS gross_profit')
            );

        if (!empty($start_date) && !empty($end_date) && $start_date != $end_date) {
            $query->whereDate('sale.transaction_date', '>=', $start_date)
                ->whereDate('sale.transaction_date', '<=', $end_date);
        }
        if (!empty($start_date) && !empty($end_date) && $start_date == $end_date) {
            $query->whereDate('sale.transaction_date', $end_date);
        }

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('sale.location_id', $permitted_locations);
        }

        //Filter by the location
        if (!empty($location_id)) {
            $query->where('sale.location_id', $location_id);
        }

        if (!empty($user_id)) {
            $query->where('sale.created_by', $user_id);
        }

        $gross_profit_obj = $query->first();

        $gross_profit = !empty($gross_profit_obj->gross_profit) ? $gross_profit_obj->gross_profit : 0;

        //KNOWS ISSUE: If products are returned then also the discount gets applied for it.

        return $gross_profit;
    }

    /**
     * Calculates reward points to be earned from an order
     *
     * @return integer
     */
    public function calculateRewardPoints($business_id, $total)
    {
        if (session()->has('business')) {
            $business = session()->get('business');
        } else {
            $business = Business::find($business_id);
        }
        $total_points = 0;

        if ($business->enable_rp == 1) {
            //check if order total elegible for reward
            if ($business->min_order_total_for_rp > $total) {
                return $total_points;
            }
            $amount_per_unit_point = $business->amount_for_unit_rp;

            $total_points = floor($total / $amount_per_unit_point);

            if (!empty($business->max_rp_per_order) && $business->max_rp_per_order < $total_points) {
                $total_points = $business->max_rp_per_order;
            }
        }

        return $total_points;
    }

    /**
     * Updates reward point of a customer
     *
     * @return void
     */
    public function updateCustomerRewardPoints(
        $customer_id,
        $earned,
        $earned_before = 0,
        $redeemed = 0,
        $redeemed_before = 0
    ) {
        $customer = Contact::find($customer_id);

        //Return if walk in customer
        if ($customer->is_default == 1) {
            return false;
        }

        $total_earned = $earned - $earned_before;
        $total_redeemed = $redeemed - $redeemed_before;

        $diff = $total_earned - $total_redeemed;

        $customer_points = empty($customer->total_rp) ? 0 : $customer->total_rp;
        $total_points = $customer_points + $diff;

        $customer->total_rp = $total_points;
        $customer->total_rp_used += $total_redeemed;
        $customer->save();
    }

    /**
     * Calculates reward points to be redeemed from an order
     *
     * @return array
     */
    public function getRewardRedeemDetails($business_id, $customer_id)
    {
        if (session()->has('business')) {
            $business = session()->get('business');
        } else {
            $business = Business::find($business_id);
        }
        $details = ['points' => 0, 'amount' => 0];

        $customer = Contact::where('business_id', $business_id)
                                        ->find($customer_id);
        $customer_reward_points = $customer->total_rp;

        //If zero reward point or walk in customer return blank values
        if (empty($customer_reward_points) || $customer->is_default == 1) {
            return $details;
        }

        $min_reward_point_required = $business->min_redeem_point;

        if (!empty($min_reward_point_required) && $customer_reward_points < $min_reward_point_required) {
            return $details;
        }

        $max_redeem_point = $business->max_redeem_point;

        if (!empty($max_redeem_point) && $max_redeem_point <= $customer_reward_points) {
            $customer_reward_points = $max_redeem_point;
        }

        $amount_per_unit_point = $business->redeem_amount_per_unit_rp;

        $equivalent_amount = $customer_reward_points * $amount_per_unit_point;

        $details = ['points' => $customer_reward_points, 'amount' => $equivalent_amount];

        return $details;
    }

    /**
     * Checks whether a reward point date is expired
     *
     * @return boolean
     */
    public function isRewardExpired($date, $business_id)
    {
        if (session()->has('business')) {
            $business = session()->get('business');
        } else {
            $business = Business::find($business_id);
        }

        $is_expired = false;

        if (!empty($business->rp_expiry_period)) {
            $expiry_date = \Carbon::parse($date);
            if ($business->rp_expiry_type == 'month') {
                $expiry_date = $expiry_date->addMonths($business->rp_expiry_period);
            } elseif ($business->rp_expiry_type == 'year') {
                $expiry_date = $expiry_date->addYears($business->rp_expiry_period);
            }

            if ($expiry_date->format('Y-m-d') >= \Carbon::now()->format('Y-m-d')) {
                $is_expired = true;
            }
        }

        return $is_expired;
    }

    /**
    * Function to delete sale
    *
    * @param int $business_id
    * @param int $transaction_id
    *
    * @return array
    */
    public function deleteSale($business_id, $transaction_id)
    {
        //Check if return exist then not allowed
        if ($this->isReturnExist($transaction_id)) {
            $output = [
                'success' => false,
                'msg' => __('lang_v1.return_exist')
            ];
            return $output;
        }

        $transaction = Transaction::where('id', $transaction_id)
                    ->where('business_id', $business_id)
                    ->whereIn('type', ['sell', 'sales_order'])
                    ->with(['sell_lines', 'payment_lines'])
                    ->first();

        if (!empty($transaction)) {
            $log_properities = [
                'id' => $transaction->id,
                'invoice_no' => $transaction->invoice_no
            ];
            $log_type = $transaction->type == 'sales_order' ? 'so_deleted' : 'sell_deleted';
            $this->activityLog($transaction, $log_type, null, $log_properities);

            //If status is draft direct delete transaction
            if ($transaction->status == 'draft') {

                foreach ($transaction->sell_lines as $sell_line) {
                    $this->updateSalesOrderLine($sell_line->so_line_id, 0, $sell_line->quantity);
                }
                $sales_order_ids = $transaction->sales_order_ids ?? [];
                if (!empty($sales_order_ids)) {
                    $this->updateSalesOrderStatus($sales_order_ids);
                }

                $transaction->delete();
            } else {
                $business = Business::findOrFail($business_id);
                $transaction_payments = $transaction->payment_lines;
                $deleted_sell_lines = $transaction->sell_lines;
                $deleted_sell_lines_ids = $deleted_sell_lines->pluck('id')->toArray();
                $this->deleteSellLines(
                    $deleted_sell_lines_ids,
                    $transaction->location_id
                );

                $this->updateCustomerRewardPoints($transaction->contact_id, 0, $transaction->rp_earned, 0, $transaction->rp_redeemed);

                $transaction->status = 'draft';
                $business_data = ['id' => $business_id,
                        'accounting_method' => $business->accounting_method,
                        'location_id' => $transaction->location_id
                    ];

                $this->adjustMappingPurchaseSell('final', $transaction, $business_data, $deleted_sell_lines_ids);

                $sales_order_ids = $transaction->sales_order_ids ?? [];
                
                if (!empty($sales_order_ids)) {
                    $this->updateSalesOrderStatus($sales_order_ids);
                }

                //Delete Cash register transactions
                $transaction->cash_register_payments()->delete();

                $transaction->delete();

                foreach ($transaction_payments as $payment) {
                    event(new TransactionPaymentDeleted($payment));
                }
            }
        }

        $output = [
                    'success' => true,
                    'msg' => __('lang_v1.sale_delete_success')
                ];

        return $output;
    }

    /**
    * common function to get
    * list purchase
    * @param int $business_id
    *
    * @return object
    */
    public function getListPurchases($business_id)
    {
        $purchases = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->join(
                        'business_locations AS BS',
                        'transactions.location_id',
                        '=',
                        'BS.id'
                    )
                    ->leftJoin(
                        'transaction_payments AS TP',
                        'transactions.id',
                        '=',
                        'TP.transaction_id'
                    )
                    ->leftJoin(
                        'transactions AS PR',
                        'transactions.id',
                        '=',
                        'PR.return_parent_id'
                    )
                    ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase')
                    ->select(
                        'transactions.id',
                        'transactions.document',
                        'transactions.transaction_date',
                        'transactions.ref_no',
                        'contacts.name',
                        'contacts.supplier_business_name',
                        'transactions.status',
                        'transactions.payment_status',
                        'transactions.final_total',
                        'BS.name as location_name',
                        'transactions.pay_term_number',
                        'transactions.pay_term_type',
                        'PR.id as return_transaction_id',
                        DB::raw('SUM(TP.amount) as amount_paid'),
                        DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE
                        TP2.transaction_id=PR.id ) as return_paid'),
                        DB::raw('COUNT(PR.id) as return_exists'),
                        DB::raw('COALESCE(PR.final_total, 0) as amount_return'),
                        DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by")
                    )
                    ->groupBy('transactions.id');

        return $purchases;
    }

    /**
    * common function to get
    * list sell
    * @param int $business_id
    *
    * @return object
    */
    public function getListSells($business_id, $sale_type = 'sell')
    {
        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                // ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->leftJoin('transaction_sell_lines as tsl', function($join) {
                    $join->on('transactions.id', '=', 'tsl.transaction_id')
                        ->whereNull('tsl.parent_sell_line_id');
                })
                ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin(
                    'transactions AS SR',
                    'transactions.id',
                    '=',
                    'SR.return_parent_id'
                )
                ->leftJoin(
                    'types_of_services AS tos',
                    'transactions.types_of_service_id',
                    '=',
                    'tos.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', $sale_type)
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.type',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'transactions.invoice_no as invoice_no_text',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.contact_id',
                    'contacts.supplier_business_name',
                    'transactions.status',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'transactions.tax_amount',
                    'transactions.discount_amount',
                    'transactions.discount_type',
                    'transactions.total_before_tax',
                    'transactions.rp_redeemed',
                    'transactions.rp_redeemed_amount',
                    'transactions.rp_earned',
                    'transactions.types_of_service_id',
                    'transactions.shipping_status',
                    'transactions.pay_term_number',
                    'transactions.pay_term_type',
                    'transactions.additional_notes',
                    'transactions.staff_note',
                    'transactions.shipping_details',
                    'transactions.document',
                    'transactions.shipping_custom_field_1',
                    'transactions.shipping_custom_field_2',
                    'transactions.shipping_custom_field_3',
                    'transactions.shipping_custom_field_4',
                    'transactions.shipping_custom_field_5',
                    'transactions.custom_field_1',
                    'transactions.custom_field_2',
                    'transactions.custom_field_3',
                    'transactions.custom_field_4',
                    DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date'),
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                    DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as total_paid'),
                    'bl.name as business_location',
                    DB::raw('COUNT(SR.id) as return_exists'),
                    DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE
                        TP2.transaction_id=SR.id ) as return_paid'),
                    DB::raw('COALESCE(SR.final_total, 0) as amount_return'),
                    'SR.id as return_transaction_id',
                    'tos.name as types_of_service_name',
                    'transactions.service_custom_field_1',
                    DB::raw('COUNT( DISTINCT tsl.id) as total_items'),
                    DB::raw("CONCAT(COALESCE(ss.surname, ''),' ',COALESCE(ss.first_name, ''),' ',COALESCE(ss.last_name,'')) as waiter"),
                    'tables.name as table_name',
                    DB::raw('SUM(tsl.quantity - tsl.so_quantity_invoiced) as so_qty_remaining'),
                    'transactions.is_export'
                );

        if ($sale_type == 'sell') {
            $sells->where('transactions.status', 'final');
        }

        return $sells;
    }

    /**
     * Function to get ledger details
     *
     */
    public function getLedgerDetails($contact_id, $start, $end, $format = 'format_1', $location_id = null)
    {
        $business_id = request()->session()->get('user.business_id');
        //Get sum of totals before start date
        $previous_transaction_sums = $this->__transactionQuery($contact_id, $start, $location_id)
                ->select(
                    DB::raw("SUM(IF(type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(type = 'sell' AND status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(type = 'opening_balance', final_total, 0)) as total_opening_balance")
                )->first();

        //Get payment totals before start date
        $prev_payments = $this->__paymentQuery($contact_id, $start, $location_id)
                            ->select('transaction_payments.*', 'bl.name as location_name', 't.type as transaction_type', 'is_advance')
                                    ->get();

        $prev_total_invoice_paid = $prev_payments->where('transaction_type', 'sell')->where('is_return', 0)->sum('amount');
        $prev_total_ob_paid = $prev_payments->where('transaction_type', 'opening_balance')->where('is_return', 0)->sum('amount');
        $prev_total_sell_change_return = $prev_payments->where('transaction_type', 'sell')->where('is_return', 1)->sum('amount');
        $prev_total_sell_change_return = !empty($prev_total_sell_change_return) ? $prev_total_sell_change_return : 0;
        $prev_total_invoice_paid -= $prev_total_sell_change_return;
        $prev_total_purchase_paid = $prev_payments->where('transaction_type', 'purchase')->where('is_return', 0)->sum('amount');
        $prev_total_sell_return_paid = $prev_payments->where('transaction_type', 'sell_return')->sum('amount');
        $prev_total_purchase_return_paid = $prev_payments->where('transaction_type', 'purchase_return')->sum('amount');
        //$prev_total_advance_payment = $prev_payments->where('is_advance', 1)->sum('amount');
        $prev_total_advance_payment = $this->__paymentQuery($contact_id, $start, $location_id)
                                        ->select('bl.name as location_name', 
                                                't.type as transaction_type', 
                                                'is_advance',
                                                'transaction_payments.id',
                                                DB::raw('(transaction_payments.amount - COALESCE((SELECT SUM(amount) from transaction_payments as TP where TP.parent_id = transaction_payments.id), 0)) as amount')
                                        )
                                        ->where('is_advance', 1)
                                        ->get()
                                        ->sum('amount');

        $total_prev_paid = $prev_total_invoice_paid + $prev_total_purchase_paid - $prev_total_sell_return_paid - $prev_total_purchase_return_paid + $prev_total_ob_paid + $prev_total_advance_payment;

        $total_prev_invoice = $previous_transaction_sums->total_purchase + $previous_transaction_sums->total_invoice -  $previous_transaction_sums->total_sell_return -  $previous_transaction_sums->total_purchase_return + $previous_transaction_sums->total_opening_balance;
        //$total_prev_paid = $prev_payments_sum->total_paid;
        $beginning_balance = $total_prev_invoice - $total_prev_paid;
                     
        $contact = Contact::find($contact_id);

        //Get transaction totals between dates
        $transaction_query = $this->__transactionQuery($contact_id, $start, $end, $location_id)
                            ->with(['location'])
                            ->select('transactions.*');

        if ($format == 'format_2') {
            $transaction_query->leftjoin('transaction_payments as tp', 'tp.transaction_id', '=', 'transactions.id')
                            ->addSelect(DB::raw('COALESCE(SUM(tp.amount), 0) as total_paid'))
                            ->groupBy('transactions.id');
        }

        $transactions = $transaction_query->get();
        $transaction_types = Transaction::transactionTypes();
        $ledger = [];

        $opening_balance = 0;
        $opening_balance_paid = 0;
        
        foreach ($transactions as $transaction) {

            if($transaction->type == 'opening_balance'){
                //Skip opening balance, it will be added in the end
                $opening_balance += $transaction->final_total;

                continue;
            }

            $temp_array = [
                'date' => $transaction->transaction_date,
                'ref_no' => in_array($transaction->type, ['sell', 'sell_return']) ? $transaction->invoice_no : $transaction->ref_no,
                'type' => $transaction_types[$transaction->type],
                'location' => $transaction->location->name,
                'payment_status' =>  __('lang_v1.' . $transaction->payment_status),
                'total' => '',
                'payment_method' => '',
                'debit' => in_array($transaction->type, ['sell', 'purchase_return'])  ? $transaction->final_total : '',
                'credit' => in_array($transaction->type, ['purchase', 'sell_return']) ? $transaction->final_total : '',
                'others' => $transaction->additional_notes
            ];

            if ($format == 'format_2') {
                $temp_array['final_total'] = $transaction->final_total;
                $temp_array['total_due'] = $transaction->final_total - $transaction->total_paid;
                $temp_array['due_date'] = $transaction->due_date;
                $temp_array['payment_status'] = $transaction->payment_status;
            }

            $ledger[] = $temp_array;
        }

        $invoice_sum = $transactions->where('type', 'sell')->sum('final_total');
        $purchase_sum = $transactions->where('type', 'purchase')->sum('final_total');
        $sell_return_sum = $transactions->where('type', 'sell_return')->sum('final_total');
        $purchase_return_sum = $transactions->where('type', 'purchase_return')->sum('final_total');

        //Get payment totals between dates
        if ($format == 'format_1') {
            $payments = $this->__paymentQuery($contact_id, $start, $end, $location_id)
                            ->select('transaction_payments.*', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no')
                            ->get();
        } else {
            $payments = [];
        }

        $paymentTypes = $this->payment_types(null, true, $business_id);

        foreach ($payments as $payment) {

            if($payment->transaction_type == 'opening_balance'){
                $opening_balance_paid += $payment->amount;
            }

            //Hide all the adjusted payments because it has already been summed as advance payment
            if (!empty($payment->parent_id)) {
                continue;
            }

            $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
            $note = $payment->note;
            if (!empty($ref_no)) {
                $note .='<small>' . __('account.payment_for') . ': ' . $ref_no . '</small>';
            }

            if ($payment->is_advance == 1) {
                $note .='<small>' . __('lang_v1.advance_payment') . '</small>';
            }

            if ($payment->is_return == 1) {
                $note .='<small>(' . __('lang_v1.change_return') . ')</small>';
            }

            $ledger[] = [
                'date' => $payment->paid_on,
                'ref_no' => $payment->payment_ref_no,
                'type' => $transaction_types['payment'],
                'location' => $payment->location_name,
                'payment_status' => '',
                'total' => '',
                'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                'payment_method_key' => $payment->method,
                'debit' => in_array($payment->transaction_type, ['purchase', 'sell_return']) || ($payment->is_advance == 1 && $contact->type == 'supplier') ? $payment->amount : '',
                'credit' => (in_array($payment->transaction_type, ['sell', 'purchase_return', 'opening_balance']) || ($payment->is_advance == 1 && in_array($contact->type, ['customer', 'both']))) && $payment->is_return == 0 ? $payment->amount : '',
                'others' =>  $note 
            ];
        }

        $total_invoice_paid = !empty($payments) ? $payments->where('transaction_type', 'sell')->where('is_return', 0)->sum('amount') : 0;
        $total_sell_change_return = !empty($payments) ? $payments->where('transaction_type', 'sell')->where('is_return', 1)->sum('amount') : 0;
        $total_sell_change_return = !empty($total_sell_change_return) ? $total_sell_change_return : 0;
        $total_invoice_paid -= $total_sell_change_return;
        $total_purchase_paid = !empty($payments) ? $payments->where('transaction_type', 'purchase')->where('is_return', 0)->sum('amount') : 0;
        $total_sell_return_paid = !empty($payments) ? $payments->where('transaction_type', 'sell_return')->sum('amount') : 0;
        $total_purchase_return_paid = !empty($payments) ? $payments->where('transaction_type', 'purchase_return')->sum('amount') : 0;

        $total_invoice_paid += $opening_balance_paid;

        $start_date = $this->format_date($start);
        $end_date = $this->format_date($end);

        $total_invoice = $invoice_sum - $sell_return_sum;
        $total_purchase = $purchase_sum - $purchase_return_sum;

        $opening_balance_due = $opening_balance - ($contact -> balance);

        $total_paid = $total_invoice_paid + $total_purchase_paid - $total_sell_return_paid - $total_purchase_return_paid;
        $curr_due = $total_invoice + $total_purchase - $total_paid + $beginning_balance + $opening_balance_due;

        //Sort by date
        if (!empty($ledger)) {
            usort($ledger, function ($a, $b) {
                $t1 = strtotime($a['date']);
                $t2 = strtotime($b['date']);
                return $t1 - $t2;
            });
        }

        $total_opening_bal = $beginning_balance + $opening_balance_due;
        if ($format != 'format_2') {
            //Add Beginning balance & openining balance to ledger
            $ledger = array_merge([[
                'date' => $start,
                'ref_no' => '',
                'type' => __('lang_v1.opening_balance') ,
                'location' => '',
                'payment_status' => '',
                'total' => '',
                'payment_method' => '',
                'debit' => $contact->type == 'customer' ? abs($total_opening_bal) : '',
                'credit' => $contact->type == 'supplier' ? abs($total_opening_bal) : '',
                'others' => '',
                'final_total' => abs($total_opening_bal),
                'total_due' => 0,
                'due_date' => null
            ]], $ledger) ;
        }
        

        $bal = 0;
        foreach($ledger as $key => $val) {
            $credit = !empty($val['credit']) ? $val['credit'] : 0;
            $debit = !empty($val['debit']) ? $val['debit'] : 0;

            if (!empty($val['payment_method_key']) && $val['payment_method_key'] == 'advance') {
                $credit = 0;
                $debit = 0;
            }
            $bal += ($credit - $debit);
            $balance = $this->num_f(abs($bal));

            if ($bal < 0) {
                $balance .= ' ' . __('lang_v1.dr');
            } else if ($bal > 0) {
                $balance .= ' ' . __('lang_v1.cr');
            }

            $ledger[$key]['balance'] = $balance;
        }

        $output = [
            'ledger' => $ledger,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_invoice' => $total_invoice,
            'total_purchase' => $total_purchase,
            'beginning_balance' => $beginning_balance + $opening_balance_due,
            'balance_due' => $curr_due,
            'total_paid' => $total_paid
        ];

        return $output;
    }

    /**
     * Query to get transaction totals for a customer
     *
     */
    private function __transactionQuery($contact_id, $start, $end = null, $location_id = null)
    {
        $business_id = request()->session()->get('user.business_id');
        $transaction_type_keys = array_keys(Transaction::transactionTypes());

        $query = Transaction::where('transactions.contact_id', $contact_id)
                        ->where('transactions.business_id', $business_id)
                        ->where('transactions.status', '!=', 'draft')
                        ->whereIn('transactions.type', $transaction_type_keys);

        if (!empty($start)  && !empty($end)) {
            $query->whereDate(
                'transactions.transaction_date',
                '>=',
                $start
            )
                ->whereDate('transactions.transaction_date', '<=', $end)->get();
        }

        if (!empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        if (!empty($start)  && empty($end)) {
            $query->whereDate('transactions.transaction_date', '<', $start);
        }

        return $query;
    }

    /**
     * Query to get payment details for a customer
     *
     */
    private function __paymentQuery($contact_id, $start, $end = null, $location_id = null)
    {
        $business_id = request()->session()->get('user.business_id');

        $query = TransactionPayment::leftJoin(
            'transactions as t',
            'transaction_payments.transaction_id',
            '=',
            't.id'
        )
            ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
            ->where('transaction_payments.payment_for', $contact_id);
            //->whereNotNull('transaction_payments.transaction_id');
            //->whereNull('transaction_payments.parent_id');

        if (!empty($start)  && !empty($end)) {
            $query->whereDate('paid_on', '>=', $start)
                        ->whereDate('paid_on', '<=', $end);
        }

        if (!empty($start)  && empty($end)) {
            $query->whereDate('paid_on', '<', $start);
        }

        if (!empty($location_id)) {
            //if location id present get all transaction with the location id and opening balance
             $query->where(function($q) use ($location_id) {
                 $q->where('transaction_payments.is_advance', 1)
                     ->orWhere('t.location_id', $location_id);
             });
        }


        return $query;
    }
    
    public function getProfitLossDetails($business_id, $location_id, $start_date, $end_date, $user_id = null)
    {
        //For Opening stock date should be 1 day before
        $day_before_start_date = \Carbon::createFromFormat('Y-m-d', $start_date)->subDay()->format('Y-m-d');

        $filters = ['user_id' => $user_id];
        //Get Opening stock
        $opening_stock = $this->getOpeningClosingStock($business_id, $day_before_start_date, $location_id, true, false, $filters);

        //Get Closing stock
        $closing_stock = $this->getOpeningClosingStock(
            $business_id,
            $end_date,
            $location_id,
            false,
            false,
            $filters
        );
                
        //Get Purchase details
        $purchase_details = $this->getPurchaseTotals(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        //Get Sell details
        $sell_details = $this->getSellTotals(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $transaction_types = [
            'purchase_return', 'sell_return', 'expense', 'stock_adjustment', 'sell_transfer', 'purchase', 'sell'
        ];

        $transaction_totals = $this->getTransactionTotals(
            $business_id,
            $transaction_types,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $gross_profit = $this->getGrossProfit(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $data['total_purchase_shipping_charge'] = !empty($purchase_details['total_shipping_charges']) ? $purchase_details['total_shipping_charges'] : 0;
        $data['total_sell_shipping_charge'] = !empty($sell_details['total_shipping_charges']) ? $sell_details['total_shipping_charges'] : 0;

        $data['total_purchase_additional_expense'] = !empty($purchase_details['total_additional_expense']) ? $purchase_details['total_additional_expense'] : 0;
        $data['total_sell_additional_expense'] = !empty($sell_details['total_additional_expense']) ? $sell_details['total_additional_expense'] : 0;
        //Shipping
        $data['total_transfer_shipping_charges'] = !empty($transaction_totals['total_transfer_shipping_charges']) ? $transaction_totals['total_transfer_shipping_charges'] : 0;
        //Discounts
        $total_purchase_discount = $transaction_totals['total_purchase_discount'];
        $total_sell_discount = $transaction_totals['total_sell_discount'];
        $total_reward_amount = $transaction_totals['total_reward_amount'];
        $total_sell_round_off = $transaction_totals['total_sell_round_off'];
        $total_sell_return_discount = $transaction_totals['total_sell_return_discount'];

        //Stocks
        $data['opening_stock'] = !empty($opening_stock) ? $opening_stock : 0;
        $data['closing_stock'] = !empty($closing_stock) ? $closing_stock : 0;

        //Purchase
        $data['total_purchase'] = !empty($purchase_details['total_purchase_exc_tax']) ? $purchase_details['total_purchase_exc_tax'] : 0;
        $data['total_purchase_discount'] = !empty($total_purchase_discount) ? $total_purchase_discount : 0;
        $data['total_purchase_return'] = $transaction_totals['total_purchase_return_exc_tax'];

        //Sales
        $data['total_sell'] = !empty($sell_details['total_sell_exc_tax']) ? $sell_details['total_sell_exc_tax'] : 0;
        $data['total_sell_discount'] = !empty($total_sell_discount) ? $total_sell_discount : 0;
        $data['total_sell_return_discount'] = !empty($total_sell_return_discount) ? $total_sell_return_discount : 0;
        $data['total_sell_return'] = $transaction_totals['total_sell_return_exc_tax'];

        $data['total_sell_round_off'] = !empty($total_sell_round_off) ? $total_sell_round_off : 0;

        //Expense
        $data['total_expense'] =  $transaction_totals['total_expense'];

        //Stock adjustments
        $data['total_adjustment'] = $transaction_totals['total_adjustment'];
        $data['total_recovered'] = $transaction_totals['total_recovered'];

        // $data['closing_stock'] = $data['closing_stock'] - $data['total_adjustment'];
        
        $data['total_reward_amount'] = !empty($total_reward_amount) ? $total_reward_amount : 0;

        $moduleUtil = new ModuleUtil();

        $module_parameters = [
            'business_id' => $business_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location_id' => $location_id,
            'user_id' => $user_id
        ];
        $modules_data = $moduleUtil->getModuleData('profitLossReportData', $module_parameters);

        $data['left_side_module_data'] = [];
        $data['right_side_module_data'] = [];
        $module_total = 0;
        if (!empty($modules_data)) {
            foreach ($modules_data as $module_data) {
                if (!empty($module_data[0])) {
                    foreach ($module_data[0] as $array) {
                        $data['left_side_module_data'][] = $array;
                        if (!empty($array['add_to_net_profit'])) {
                            $module_total -= $array['value'];
                        }
                    }
                }
                if (!empty($module_data[1])) {
                    foreach ($module_data[1] as $array) {
                        $data['right_side_module_data'][] = $array;
                        if (!empty($array['add_to_net_profit'])) {
                            $module_total += $array['value'];
                        }
                    }
                }
            }
        }

        // $data['net_profit'] = $module_total + $data['total_sell']
        //                         + $data['closing_stock']
        //                         - $data['total_purchase']
        //                         - $data['total_sell_discount']
        //                         + $data['total_sell_round_off']
        //                         - $data['total_reward_amount']
        //                         - $data['opening_stock']
        //                         - $data['total_expense']
        //                         + $data['total_recovered']
        //                         - $data['total_transfer_shipping_charges']
        //                         - $data['total_purchase_shipping_charge']
        //                         + $data['total_sell_shipping_charge']
        //                         + $data['total_purchase_discount']
        //                         + $data['total_purchase_return']
        //                         - $data['total_sell_return'];
        $data['net_profit'] = $module_total + $gross_profit
                                + ($data['total_sell_round_off'] + $data['total_recovered'] + $data['total_sell_shipping_charge'] + $data['total_purchase_discount'] + $data['total_sell_additional_expense'] + $data['total_sell_return_discount']
                                ) - ( $data['total_reward_amount'] + $data['total_expense'] + $data['total_adjustment'] + $data['total_transfer_shipping_charges'] + $data['total_purchase_shipping_charge'] + $data['total_purchase_additional_expense'] + $data['total_sell_discount']
                                );

        //get gross profit from Project Module
        $module_parameters = [
            'business_id' => $business_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location_id' => $location_id
        ];
        $project_module_data = $moduleUtil->getModuleData('grossProfit', $module_parameters);

        if (!empty($project_module_data['Project']['gross_profit'])) {
            $gross_profit = $gross_profit + $project_module_data['Project']['gross_profit'];
            $data['gross_profit_label'] = __('project::lang.project_invoice');
        }

        $data['gross_profit'] = $gross_profit;

        //get sub type for total sales
        $sales_by_subtype = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final');
        if (!empty($start_date) && !empty($end_date)) {
            if ($start_date == $end_date) {
                $sales_by_subtype->whereDate('transaction_date', $end_date);
            } else {
                $sales_by_subtype->whereBetween(DB::raw('transaction_date'), [$start_date, $end_date]);
            }
        }
        $sales_by_subtype = $sales_by_subtype->select(DB::raw('SUM(total_before_tax) as total_before_tax'), 'sub_type')
            ->whereNotNull('sub_type')
            ->groupBy('transactions.sub_type')
            ->get();
        $data['total_sell_by_subtype'] = $sales_by_subtype;

        return $data;
    }

    /**
     * Creates recurring expense from existing expense
     * @param obj $transaction
     *
     * @return obj $recurring_invoice
     */
    public function createRecurringExpense($transaction)
    {
        $data = $transaction->toArray();

        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        unset($data['ref_no']);
        $data['payment_status'] = 'due';
        $data['recur_parent_id'] = $transaction->id;
        $data['is_recurring'] = 0;
        $data['recur_interval'] = null;
        $data['recur_interval_type'] = null;
        $data['recur_repetitions'] = 0;
        $data['recur_stopped_on'] = null;
        $data['transaction_date'] = \Carbon::now();

        if (isset($data['recurring_invoices'])) {
            unset($data['recurring_invoices']);
        }

        if (isset($data['business'])) {
            unset($data['business']);
        }

        //Update reference count
        $ref_count = $this->setAndGetReferenceCount('expense', $transaction->business_id);
        //Generate reference number
        $data['ref_no'] = $this->generateReferenceNumber('expense', $ref_count, $transaction->business_id);

        $recurring_expense = Transaction::create($data);

        return $recurring_expense;
    }

    public function createExpense($request, $business_id, $user_id, $format_data = true)
    {
        $transaction_data = $request->only([ 'ref_no', 'transaction_date', 
            'location_id', 'final_total', 'expense_for', 'additional_notes', 
            'expense_category_id', 'tax_id', 'contact_id']);
            
        $transaction_data['business_id'] = $business_id;
        $transaction_data['created_by'] = $user_id;
        $transaction_data['type'] = !empty($request->input('is_refund')) && $request->input('is_refund') == 1 ? 'expense_refund' : 'expense';
        $transaction_data['status'] = 'final';
        $transaction_data['payment_status'] = 'due';
        $transaction_data['final_total'] = $format_data ? $this->num_uf(
                $transaction_data['final_total']
            ) : $transaction_data['final_total'];
        if ($request->has('transaction_date')) {
            $transaction_data['transaction_date'] = $format_data ? $this->uf_date($transaction_data['transaction_date'], true) : $transaction_data['transaction_date'];
        } else {
            $transaction_data['transaction_date'] = \Carbon::now();
        }

        if ($request->has('expense_sub_category_id')) {
            $transaction_data['expense_sub_category_id'] = $request->input('expense_sub_category_id');
        }

        $transaction_data['total_before_tax'] = $transaction_data['final_total'];
        if (!empty($transaction_data['tax_id'])) {
            $tax_details = TaxRate::find($transaction_data['tax_id']);
            $transaction_data['total_before_tax'] = $this->calc_percentage_base($transaction_data['final_total'], $tax_details->amount);
            $transaction_data['tax_amount'] = $transaction_data['final_total'] - $transaction_data['total_before_tax'];
        }

        if ($request->has('is_recurring')) {
            $transaction_data['is_recurring'] = 1;
            $transaction_data['recur_interval'] = !empty($request->input('recur_interval')) ? $request->input('recur_interval') : 1;
            $transaction_data['recur_interval_type'] = $request->input('recur_interval_type');
            $transaction_data['recur_repetitions'] = $request->input('recur_repetitions');
            $transaction_data['subscription_repeat_on'] = $request->input('recur_interval_type') == 'months' && !empty($request->input('subscription_repeat_on')) ? $request->input('subscription_repeat_on') : null;
        }

        //Update reference count
        $ref_count = $this->setAndGetReferenceCount('expense', $business_id);
        //Generate reference number
        if (empty($transaction_data['ref_no'])) {
            $transaction_data['ref_no'] = $this->generateReferenceNumber('expense', $ref_count, $business_id);
        }

        //upload document
        $document_name = $this->uploadFile($request, 'document', 'documents');
        if (!empty($document_name)) {
            $transaction_data['document'] = $document_name;
        }

        $transaction = Transaction::create($transaction_data);
        
        $payments = !empty($request->input('payment')) ? $request->input('payment') : [];
        //add expense payment
        $this->createOrUpdatePaymentLines($transaction, $payments, $business_id);

        //update payment status
        $this->updatePaymentStatus($transaction->id, $transaction->final_total);

        return $transaction;
    }

    public function updateExpense($request, $id, $business_id, $format_data = true)
    {
        $transaction_data = [];
        $transaction = Transaction::where('business_id', $business_id)
                                ->findOrFail($id);

        if ($request->has('ref_no')) {
            $transaction_data['ref_no'] = $request->input('ref_no');
        }
        if ($request->has('expense_for')) {
            $transaction_data['expense_for'] = $request->input('expense_for');
        }
        if ($request->has('contact_id')) {
            $transaction_data['contact_id'] = $request->input('contact_id');
        }
        if ($request->has('transaction_date')) {
            $transaction_data['transaction_date'] = $format_data ? $this->uf_date($request->input('transaction_date'), true) : $request->input('transaction_date');
        }
        if ($request->has('location_id')) {
            $transaction_data['location_id'] = $request->input('location_id');
        }
        if ($request->has('additional_notes')) {
            $transaction_data['additional_notes'] = $request->input('additional_notes');
        }

        if ($request->has('expense_sub_category_id')) {
            $transaction_data['expense_sub_category_id'] = $request->input('expense_sub_category_id');
        }

        if ($request->has('expense_category_id')) {
            $transaction_data['expense_category_id'] = $request->input('expense_category_id');
        }
        $final_total = $request->has('final_total') ? $request->input('final_total') : $transaction->final_total;
        if ($request->has('final_total')) {
            $transaction_data['final_total'] = $format_data ? $this->num_uf(
                $final_total
            ) : $final_total;
            $final_total = $transaction_data['final_total'];
        }

        $transaction_data['total_before_tax'] = $transaction_data['final_total'];
        $tax_id = !empty($request->input('tax_id')) ? $request->input('tax_id') : $transaction->tax_id;
        if (!empty($tax_id)) {
            $transaction_data['tax_id'] = $tax_id;
            $tax_details = TaxRate::find($tax_id);
            $transaction_data['total_before_tax'] = $this->calc_percentage_base($final_total, $tax_details->amount);
            $transaction_data['tax_amount'] = $final_total - $transaction_data['total_before_tax'];
        } else {
            $transaction_data['tax_id'] = null;
            $transaction_data['tax_amount'] = 0;

        }

        //upload document
        $document_name = $this->uploadFile($request, 'document', 'documents');
        if (!empty($document_name)) {
            $transaction_data['document'] = $document_name;
        }

        $transaction_data['is_recurring'] = $request->has('is_recurring') ? 1 : 0;
        $transaction_data['recur_interval'] = !empty($request->input('recur_interval')) ? $request->input('recur_interval') : 0;
        $transaction_data['recur_interval_type'] = !empty($request->input('recur_interval_type')) ? $request->input('recur_interval_type') : $transaction->recur_interval_type;
        $transaction_data['recur_repetitions'] = !empty($request->input('recur_repetitions')) ? $request->input('recur_repetitions') : $transaction->recur_repetitions;
        $transaction_data['subscription_repeat_on'] = !empty($request->input('subscription_repeat_on')) ? $request->input('subscription_repeat_on') : $transaction->subscription_repeat_on;

        $transaction->update($transaction_data);
        $transaction->save();

        //update payment status
        $this->updatePaymentStatus($transaction->id, $transaction->final_total);

        return $transaction;
    }

    /**
     * Updates contact balance
     * @param obj $contact
     * @param float $amount
     * @param string $type [add, deduct]
     *
     * @return obj $recurring_invoice
     */
    function updateContactBalance($contact, $amount, $type = 'add')
    {
        if (!is_object($contact)) {
            $contact = Contact::findOrFail($contact);
        }

        if ($type == 'add') {
           $contact->balance += $amount;
        } elseif ($type == 'deduct') {
            $contact->balance -= $amount;
        }
        $contact->save();
    }

    public function payContact($request, $format_data = true)
    {
        $contact_id = $request->input('contact_id');
        $business_id = auth()->user()->business_id;
        $inputs = $request->only(['amount', 'method', 'note', 'card_number', 'card_holder_name',
            'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
            'cheque_number', 'bank_account_number']);

        $payment_types = $this->payment_types();

        if (!array_key_exists($inputs['method'], $payment_types)) {
            throw new \Exception("Payment method not found");
        }
        $inputs['paid_on'] = $request->input('paid_on', \Carbon::now()->toDateTimeString());
        if ($format_data) {
            $inputs['paid_on'] = $this->uf_date($inputs['paid_on'], true);
            $inputs['amount'] = $this->num_uf($inputs['amount']);
        }
        
       
        $inputs['created_by'] = auth()->user()->id;
        $inputs['payment_for'] = $contact_id;
        $inputs['business_id'] = $business_id;
        $inputs['is_advance'] = 1;

        for ($i=1; $i<8; $i++) { 
            if ($inputs['method'] == 'custom_pay_' . $i) {
                $inputs['transaction_no'] =  $request->input("transaction_no_{$i}");
            }
        }

        $contact = Contact::where('business_id', $business_id)
                        ->findOrFail($contact_id);

        $due_payment_type = $request->input('due_payment_type');
        if (empty($due_payment_type)) {
            $due_payment_type = $contact->type == 'supplier' ? 'purchase' : 'sell';
        }
        
        $prefix_type = '';
        if ($contact->type == 'customer') {
            $prefix_type = 'sell_payment';
        } else if ($contact->type == 'supplier') {
            $prefix_type = 'purchase_payment';
        }
        $ref_count = $this->setAndGetReferenceCount($prefix_type, $business_id);
        //Generate reference number
        $payment_ref_no = $this->generateReferenceNumber($prefix_type, $ref_count, $business_id);

        $inputs['payment_ref_no'] = $payment_ref_no;

        if (!empty($request->input('account_id'))) {
            $inputs['account_id'] = $request->input('account_id');
        }

        //Upload documents if added
        $inputs['document'] = $this->uploadFile($request, 'document', 'documents');
        
        $parent_payment = TransactionPayment::create($inputs);

        $inputs['transaction_type'] = $due_payment_type;
        event(new TransactionPaymentAdded($parent_payment, $inputs));

        //Distribute above payment among unpaid transactions
        $excess_amount = $this->payAtOnce($parent_payment, $due_payment_type);
        //Update excess amount
        if (!empty($excess_amount)) {
            $this->updateContactBalance($contact, $excess_amount);
        }

        return $parent_payment;
    }

    public function addSellReturn($input, $business_id, $user_id, $uf_number = true)
    {
        $discount = [
                'discount_type' => $input['discount_type'] ?? 'fixed',
                'discount_amount' => $input['discount_amount'] ?? 0
            ];

        $business = Business::with(['currency'])->findOrFail($business_id);

        $productUtil = new \App\Utils\ProductUtil();

        $input['tax_id'] = $input['tax_id'] ?? null;

        $invoice_total = $productUtil->calculateInvoiceTotal($input['products'], $input['tax_id'], $discount, $uf_number);

        //Get parent sale
        $sell = Transaction::where('business_id', $business_id)
                        ->with(['sell_lines', 'sell_lines.sub_unit'])
                        ->findOrFail($input['transaction_id']);

        //Check if any sell return exists for the sale
        $sell_return = Transaction::where('business_id', $business_id)
                ->where('type', 'sell_return')
                ->where('return_parent_id', $sell->id)
                ->first();

        $sell_return_data = [
            'invoice_no' => $input['invoice_no'] ?? null,
            'discount_type' => $discount['discount_type'],
            'discount_amount' => $uf_number ? $this->num_uf($discount['discount_amount']) : $discount['discount_amount'],
            'tax_id' => $input['tax_id'],
            'tax_amount' => $invoice_total['tax'],
            'total_before_tax' => $invoice_total['total_before_tax'],
            'final_total' => $invoice_total['final_total']
        ];

        if (!empty($input['transaction_date'])) {
            $sell_return_data['transaction_date'] = $uf_number ? $this->uf_date($input['transaction_date'], true) : $input['transaction_date'];
        }
        
        //Generate reference number
        if (empty($sell_return_data['invoice_no']) && empty($sell_return)) {
            //Update reference count
            $ref_count = $this->setAndGetReferenceCount('sell_return', $business_id);
            $sell_return_data['invoice_no'] = $this->generateReferenceNumber('sell_return', $ref_count, $business_id);
        }

        if (empty($sell_return)) {
            $sell_return_data['transaction_date'] = $sell_return_data['transaction_date'] ?? \Carbon::now();
            $sell_return_data['business_id'] = $business_id;
            $sell_return_data['location_id'] = $sell->location_id;
            $sell_return_data['contact_id'] = $sell->contact_id;
            $sell_return_data['customer_group_id'] = $sell->customer_group_id;
            $sell_return_data['type'] = 'sell_return';
            $sell_return_data['status'] = 'final';
            $sell_return_data['created_by'] = $user_id;
            $sell_return_data['return_parent_id'] = $sell->id;
            $sell_return = Transaction::create($sell_return_data);

            $this->activityLog($sell_return, 'added');
        } else {
            $sell_return_data['invoice_no'] = $sell_return_data['invoice_no'] ?? $sell_return->invoice_no;
            $sell_return_before = $sell_return->replicate();
            
            $sell_return->update($sell_return_data);

            $this->activityLog($sell_return, 'edited', $sell_return_before);
        }

        if ($business->enable_rp == 1 && !empty($sell->rp_earned)) {
            $is_reward_expired = $this->isRewardExpired($sell->transaction_date, $business_id);
            if (!$is_reward_expired) {
                $diff = $sell->final_total - $sell_return->final_total;
                $new_reward_point = $this->calculateRewardPoints($business_id, $diff);
                $this->updateCustomerRewardPoints($sell->contact_id, $new_reward_point, $sell->rp_earned);

                $sell->rp_earned = $new_reward_point;
                $sell->save();
            }
        }

        //Update payment status
        $this->updatePaymentStatus($sell_return->id, $sell_return->final_total);

        //Update quantity returned in sell line
        $returns = [];
        $product_lines = $input['products'];
        foreach ($product_lines as $product_line) {
            $returns[$product_line['sell_line_id']] = $uf_number ? $this->num_uf($product_line['quantity']) : $product_line['quantity'];
        }
        foreach ($sell->sell_lines as $sell_line) {
            if (array_key_exists($sell_line->id, $returns)) {
                $multiplier = 1;
                if (!empty($sell_line->sub_unit)) {
                    $multiplier = $sell_line->sub_unit->base_unit_multiplier;
                }

                $quantity = $returns[$sell_line->id] * $multiplier;

                $quantity_before = $sell_line->quantity_returned;

                $sell_line->quantity_returned = $quantity;
                $sell_line->save();

                //update quantity sold in corresponding purchase lines
                $this->updateQuantitySoldFromSellLine($sell_line, $quantity, $quantity_before, false);

                // Update quantity in variation location details
                $productUtil->updateProductQuantity($sell_return->location_id, $sell_line->product_id, $sell_line->variation_id, $quantity, $quantity_before, null, false);
            }
        }

        return $sell_return;     
    }

    public function updatePurchaseOrderStatus($purchase_order_ids = [])
    {
        foreach ($purchase_order_ids as $purchase_order_id) {
            $purchase_order = Transaction::with(['purchase_lines'])->find($purchase_order_id);

            if (empty($purchase_order)) {
                continue;
            }
            $total_ordered = $purchase_order->purchase_lines->sum('quantity');
            $total_received = $purchase_order->purchase_lines->sum('po_quantity_purchased');

            $status = $total_received == 0 ? 'ordered' : 'partial';
            if ($total_ordered == $total_received) {
                $status = 'completed';
            }
            $purchase_order->status = $status;
            $purchase_order->save();
        }
    }

    /**
     * Get pdf content for given 
     * transaction id
     *
     * @param int $business_id
     * @param int $transaction_id
     *
     * @return array
     */
    public function getPdfContentsForGivenTransaction($business_id, $transaction_id)
    {
        //Get business details
        $businessUtil = new BusinessUtil();

        $business_details = $businessUtil->getDetails($business_id);

        //Get transaction
        $transaction = Transaction::findOrFail($transaction_id);

        //Get business location details
        $location_details = BusinessLocation::find($transaction->location_id);

        //Get invoice layout
        $invoice_layout_id = $location_details->invoice_layout_id;
        $invoice_layout = $businessUtil->invoiceLayout($business_id, $transaction->location_id, $invoice_layout_id);

        //Check if printer setting is provided.
        $receipt_printer_type = $location_details->receipt_printer_type;
        
        //Get receipt details
        $receipt_details = $this->getReceiptDetails($transaction_id, $transaction->location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);
        $currency_details = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator,
        ];
        $receipt_details->currency = $currency_details;

        return [
            'location_details' => $location_details,
            'receipt_details' => $receipt_details
        ];
    }

    /**
     * Return mpdf object for 
     * email attachment
     *
     * @param int $business_id
     * @param int $transaction_id
     * @param boolean $is_email_attachment
     *
     * @return object
     */
    public function getEmailAttachmentForGivenTransaction($business_id, $transaction_id, $is_email_attachment)
    {
        $receipt_contents = $this->getPdfContentsForGivenTransaction($business_id, $transaction_id);

        $receipt_details = $receipt_contents['receipt_details'];
        $location_details = $receipt_contents['location_details'];

        $blade_file = 'download_pdf';
        if (!empty($receipt_details->is_export)) {
            $blade_file = 'download_export_pdf';
        }
        
        //Generate pdf
        $body = view('sale_pos.receipts.'.$blade_file)
                    ->with(compact('receipt_details', 'location_details', 'is_email_attachment'))
                    ->render();

        $mpdf = new \Mpdf\Mpdf(['tempDir' => public_path('uploads/temp'), 
                    'mode' => 'utf-8', 
                    'autoScriptToLang' => true,
                    'autoLangToFont' => true,
                    'autoVietnamese' => true,
                    'autoArabic' => true,
                    'margin_top' => 8,
                    'margin_bottom' => 8,
                    'format' => 'A4'
                ]);
        
        $mpdf->useSubstitutions=true;
        $mpdf->SetWatermarkText($receipt_details->business_name, 0.1);
        $mpdf->showWatermarkText = true;
        $mpdf->SetTitle('INVOICE-'.$receipt_details->invoice_no.'.pdf');
        $mpdf->WriteHTML($body);
        return $mpdf;

    }

    public function updateSalesOrderStatus($sales_order_ids = [])
    {
        foreach ($sales_order_ids as $sales_order_id) {
            $sales_order = Transaction::with(['sell_lines'])->find($sales_order_id);

            if (empty($sales_order)) {
                continue;
            }
            $total_ordered = $sales_order->sell_lines->sum('quantity');
            $total_received = $sales_order->sell_lines->sum('so_quantity_invoiced');

            $status = $total_received == 0 ? 'ordered' : 'partial';
            if ($total_ordered == $total_received) {
                $status = 'completed';
            }
            $sales_order->status = $status;
            $sales_order->save();
        }
    }

    public function getUserTotalSales($business_id, $user_id, $start_date, $end_date)
    {
        $totals = Transaction::where('business_id', $business_id)
                                ->where('commission_agent', $user_id)
                                ->where('type', 'sell')
                                ->where('status', 'final')
                                ->whereBetween(DB::raw('transaction_date'), [$start_date, $end_date])
                                ->select(
                                    DB::raw('SUM(final_total) as total_sales'),
                                    DB::raw('SUM(total_before_tax - shipping_charges - (SELECT SUM(item_tax*quantity) FROM transaction_sell_lines as tsl WHERE tsl.transaction_id=transactions.id) ) as total_sales_without_tax')
                                )
                                ->first();

        return [
            'total_sales' => $totals->total_sales ?? 0,
            'total_sales_without_tax' => $totals->total_sales_without_tax ?? 0
        ];
    }

    public function getSources($business_id)
    {
        $unique_sources = Transaction::where('business_id', $business_id)
                                    ->where('type', 'sell')
                                    ->select('source')
                                    ->groupBy('source')
                                    ->get();
        $sources = [];

        foreach ($unique_sources as $source) {
            if (!empty($source->source)) {
                $sources[$source->source] = $source->source;
            }
        }

        return $sources;
    }
}
