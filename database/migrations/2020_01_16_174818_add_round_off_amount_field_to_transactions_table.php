<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoundOffAmountFieldToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('round_off_amount', 22, 4)->default(0)->after('staff_note')->comment('Difference of rounded total and actual total');
        });

        Schema::table('invoice_layouts', function (Blueprint $table) {
            $table->string('round_off_label')->nullable()->after('total_label');
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
