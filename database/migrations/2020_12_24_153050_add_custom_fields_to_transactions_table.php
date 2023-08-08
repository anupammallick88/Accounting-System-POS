<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomFieldsToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('custom_field_1')->nullable()->after('created_by');
            $table->string('custom_field_2')->nullable()->after('custom_field_1');
            $table->string('custom_field_3')->nullable()->after('custom_field_2');
            $table->string('custom_field_4')->nullable()->after('custom_field_3');
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
