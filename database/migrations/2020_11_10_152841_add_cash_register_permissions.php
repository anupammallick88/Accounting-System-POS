<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddCashRegisterPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exising_permissions = Permission::whereIn('name', 
                            ['view_cash_register', 'close_cash_register'])
                                    ->pluck('name')
                                    ->toArray();
                                    
        if (!in_array('view_cash_register', $exising_permissions)) {
            Permission::create(['name' => 'view_cash_register']);
        } 

        if (!in_array('close_cash_register', $exising_permissions)) {
            Permission::create(['name' => 'close_cash_register']);
        }                     
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
