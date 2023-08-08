<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeTypeColumnToStringInTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type VARCHAR(191) DEFAULT NULL");
        DB::statement("ALTER TABLE transactions ADD INDEX (type);");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement("ALTER TABLE  transactions CHANGE  location_id  location_id INT( 10 ) UNSIGNED NULL ;");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
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
