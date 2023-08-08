<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexingForMultipleColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function(Blueprint $table)
        {
            $table->index('business_id');
            $table->index('account_type_id');
            $table->index('created_by');
        });

        Schema::table('account_transactions', function(Blueprint $table)
        {
            $table->index('type');
            $table->index('sub_type');
        });

        Schema::table('account_types', function(Blueprint $table)
        {
            $table->index('parent_account_type_id');
            $table->index('business_id');
        });

        Schema::table('bookings', function(Blueprint $table)
        {
            $table->index('correspondent_id');
        });

        Schema::table('business_locations', function(Blueprint $table)
        {
            $table->index('sale_invoice_layout_id');
            $table->index('selling_price_group_id');
            $table->index('receipt_printer_type');
            $table->index('printer_id');
        });

        Schema::table('cash_register_transactions', function(Blueprint $table)
        {
            $table->index('type');
            $table->index('transaction_type');
        });

        Schema::table('categories', function(Blueprint $table)
        {
            $table->index('parent_id');
        });

        Schema::table('customer_groups', function(Blueprint $table)
        {
            $table->index('created_by');
        });

        Schema::table('discount_variations', function(Blueprint $table)
        {
            $table->index('discount_id');
            $table->index('variation_id');
        });

        Schema::table('invoice_schemes', function(Blueprint $table)
        {
            $table->index('scheme_type');
        });

        Schema::table('media', function(Blueprint $table)
        {
            $table->index('business_id');
            $table->index('uploaded_by');
        });

        Schema::table('products', function(Blueprint $table)
        {
            $table->index('type');
            $table->index('tax_type');
            $table->index('barcode_type');
        });

        Schema::table('product_racks', function(Blueprint $table)
        {
            $table->index('business_id');
            $table->index('location_id');
            $table->index('product_id');
        });

        Schema::table('reference_counts', function(Blueprint $table)
        {
            $table->index('business_id');
        });
        Schema::table('stock_adjustment_lines', function(Blueprint $table)
        {
            $table->index('lot_no_line_id');
        });
        Schema::table('transactions', function(Blueprint $table)
        {
            $table->index('res_table_id');
            $table->index('res_waiter_id');
            $table->index('res_order_status');
            $table->index('payment_status');
            $table->index('discount_type');
            $table->index('commission_agent');
            $table->index('transfer_parent_id');
            $table->index('types_of_service_id');
            $table->index('packing_charge_type');
            $table->index('recur_parent_id');
            $table->index('selling_price_group_id');
        });

        Schema::table('transaction_sell_lines', function(Blueprint $table)
        {
            $table->index('line_discount_type');
            $table->index('discount_id');
            $table->index('lot_no_line_id');
            $table->index('sub_unit_id');
        });

        Schema::table('user_contact_access', function(Blueprint $table)
        {
            $table->index('user_id');
            $table->index('contact_id');
        });

        Schema::table('warranties', function(Blueprint $table)
        {
            $table->index('business_id');
            $table->index('duration_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
