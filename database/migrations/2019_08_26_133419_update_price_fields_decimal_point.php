<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePriceFieldsDecimalPoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Get all columns with type decimal(20, 2)
        $db_name = env('DB_DATABASE');

        $columns = DB::select("SELECT distinct table_name, 
            column_name, data_type, column_default
            from information_schema.columns
            where data_type='decimal'
            and table_schema='$db_name'
            and numeric_scale=2 
            and numeric_precision=20");

        //Alter all columns
        foreach ($columns as $col) {
            if(!empty($col->table_name)){
                $table_name = $col->table_name;
                $col_name = $col->column_name;
                $default = is_null($col->column_default) ? 'NULL' : $col->column_default;

                DB::statement("ALTER TABLE $table_name MODIFY COLUMN $col_name DECIMAL(22, 4) DEFAULT $default");
            }
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
