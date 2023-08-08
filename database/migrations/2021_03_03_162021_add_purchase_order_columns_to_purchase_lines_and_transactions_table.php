<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPurchaseOrderColumnsToPurchaseLinesAndTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->integer('purchase_order_line_id')->after('tax_id')->nullable();
            $table->decimal('po_quantity_purchased', 22, 4)->after('quantity_returned')->default(0);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('purchase_order_ids')->after('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
