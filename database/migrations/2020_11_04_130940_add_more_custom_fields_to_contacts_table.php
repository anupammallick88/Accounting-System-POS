<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreCustomFieldsToContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('custom_field5')->nullable()->after('custom_field4');
            $table->string('custom_field6')->nullable()->after('custom_field5');
            $table->string('custom_field7')->nullable()->after('custom_field6');
            $table->string('custom_field8')->nullable()->after('custom_field7');
            $table->string('custom_field9')->nullable()->after('custom_field8');
            $table->string('custom_field10')->nullable()->after('custom_field9');
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
