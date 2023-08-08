<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalesOrderColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            $table->integer('so_line_id')->after('sell_line_note')->nullable();
            $table->decimal('so_quantity_invoiced', 22, 4)->after('so_line_id')->default(0);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('sales_order_ids')->after('created_by')->nullable();
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
