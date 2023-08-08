<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAccountTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('parent_account_type_id')->nullable();
            $table->integer('business_id');
            $table->timestamps();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('account_type_id')->nullable()->after('account_number');
        });

        DB::statement('ALTER TABLE accounts DROP COLUMN account_type;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_types');
    }
}
