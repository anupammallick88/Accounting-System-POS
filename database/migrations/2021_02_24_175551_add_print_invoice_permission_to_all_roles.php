<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AddPrintInvoicePermissionToAllRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //check if permission exists
        $permission_exists = Permission::where('name', 'print_invoice')
                                    ->exists();
                                    
        if (!$permission_exists) {
           Permission::create([
                'name' => 'print_invoice',
                'guard_name' => 'web'
            ]);
        }
        $roles = Role::all();

        foreach($roles as $role) {
            $role->givePermissionTo('print_invoice');
        }
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
