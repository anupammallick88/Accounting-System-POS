<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubUnitsRelatedFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('sub_unit_ids')->nullable()->after('unit_id');
        });

        Schema::table('business', function (Blueprint $table) {
            $table->boolean('enable_sub_units')->default(false)->after('default_unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sub_unit_ids');
        });

        Schema::table('business', function (Blueprint $table) {
            $table->dropColumn('enable_sub_units');
        });
    }
}
