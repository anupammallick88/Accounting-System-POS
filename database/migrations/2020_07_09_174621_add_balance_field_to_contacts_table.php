<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBalanceFieldToContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->decimal('balance', 22, 4)->default(0)->after('created_by');
        });

        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->boolean('is_advance')->default(0)->after('created_by');
        });

        DB::statement("ALTER TABLE transaction_payments MODIFY COLUMN `method` VARCHAR(191) DEFAULT NULL;");
        DB::statement("ALTER TABLE cash_register_transactions MODIFY COLUMN `pay_method` VARCHAR(191) DEFAULT NULL;");
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
