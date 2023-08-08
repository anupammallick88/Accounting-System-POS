<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeQuantityFieldTypesToDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE purchase_lines MODIFY COLUMN quantity DECIMAL(22, 4) NOT NULL DEFAULT  '0'");

        DB::statement("ALTER TABLE transaction_sell_lines MODIFY COLUMN quantity DECIMAL(22, 4) NOT NULL DEFAULT  '0'");

        DB::statement("ALTER TABLE transactions MODIFY COLUMN discount_amount DECIMAL(22, 4) DEFAULT  '0'");
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
