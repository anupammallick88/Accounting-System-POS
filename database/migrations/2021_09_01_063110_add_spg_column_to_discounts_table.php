<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSpgColumnToDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE discounts DROP COLUMN applicable_in_spg");

        Schema::table('discounts', function (Blueprint $table) {
            $table->string('spg', 100)->nullable()->after('is_active')->comment("Applicable in specified selling price group only. Use of applicable_in_spg column is discontinued")->index();
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
