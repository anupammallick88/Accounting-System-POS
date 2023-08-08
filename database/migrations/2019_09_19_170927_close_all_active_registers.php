<?php

use App\CashRegister;
use Illuminate\Database\Migrations\Migration;

class CloseAllActiveRegisters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CashRegister::where('status', 'open')
                ->whereNull('location_id')
                ->update([
                    'closed_at' => \Carbon::now()->format('Y-m-d H:i:s'),
                    'status' => 'close'
                ]);
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
