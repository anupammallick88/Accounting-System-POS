<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeColumnsToBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->string('code_label_1')
                ->after('tax_label_2')
                ->nullable();

            $table->string('code_1')
                ->after('code_label_1')
                ->nullable();

            $table->string('code_label_2')
                ->after('code_1')
                ->nullable();

            $table->string('code_2')
                ->after('code_label_2')
                ->nullable();
        });
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
