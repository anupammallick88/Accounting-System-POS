<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPaymentLinkFieldsToTransactionPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE transaction_payments MODIFY COLUMN created_by INT(11) DEFAULT NULL");

        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->boolean('paid_through_link')->default(0)->after('created_by');
            $table->string('gateway')->nullable()->after('paid_through_link');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            //
        });
    }
}
