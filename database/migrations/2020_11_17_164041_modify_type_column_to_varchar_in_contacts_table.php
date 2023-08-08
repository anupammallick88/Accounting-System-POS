<?php

use Illuminate\Database\Migrations\Migration;
use App\Contact;

class ModifyTypeColumnToVarcharInContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE contacts MODIFY COLUMN `type` VARCHAR(191) NOT NULL");
        
        Contact::where('type', '=', '')
                 ->orWhereNull('type')
                ->update(['type' => 'lead']);
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
