<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class RemoveLocationPermissionsFromRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $location_permissions = Permission::where('name', 'like', 'location.%')
                                            ->orWhere('name', 'access_all_locations')
                                            ->pluck('id');

        DB::table('role_has_permissions')
            ->whereIn('permission_id', $location_permissions)
            ->delete();

        Artisan::call('cache:clear');
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
