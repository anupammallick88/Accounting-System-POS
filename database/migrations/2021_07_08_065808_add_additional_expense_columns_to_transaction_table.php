<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalExpenseColumnsToTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('additional_expense_key_1')->nullable()->after('round_off_amount');
            $table->decimal('additional_expense_value_1', 22, 4)->default(0)->after('additional_expense_key_1');

            $table->string('additional_expense_key_2')->nullable()->after('additional_expense_value_1');
            $table->decimal('additional_expense_value_2', 22, 4)->default(0)->after('additional_expense_key_2');

            $table->string('additional_expense_key_3')->nullable()->after('additional_expense_value_2');
            $table->decimal('additional_expense_value_3', 22, 4)->default(0)->after('additional_expense_key_3');

            $table->string('additional_expense_key_4')->nullable()->after('additional_expense_value_3');
            $table->decimal('additional_expense_value_4', 22, 4)->default(0)->after('additional_expense_key_4');
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
