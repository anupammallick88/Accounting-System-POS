<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Transaction;

class ChangeCompletedStockTransferStatusToFinal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('transactions', function(Blueprint $table) {
            $table->index('status');
        });

        Transaction::where('type', 'sell_transfer')
                ->where('status', 'completed')
                ->update(['status' => 'final']);

        Transaction::where('type', 'purchase_transfer')
                ->where('status', 'completed')
                ->update(['status' => 'received']);
    }
}
