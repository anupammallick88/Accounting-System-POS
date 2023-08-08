<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashRegisterTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cash_register_id')->unsigned();
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')
                    ->onDelete('cascade');
            $table->decimal('amount', 22, 4)->default(0);
            $table->enum('pay_method', ['cash', 'card', 'cheque', 'bank_transfer', 'other']);
            $table->enum('type', ['debit', 'credit']);
            $table->enum('transaction_type', ['initial', 'sell', 'transfer', 'refund']);
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_register_transactions');
    }
}
