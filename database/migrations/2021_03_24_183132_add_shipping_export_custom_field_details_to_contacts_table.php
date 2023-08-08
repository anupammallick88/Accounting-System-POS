<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShippingExportCustomFieldDetailsToContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->longText('shipping_custom_field_details')
                ->nullable()
                ->after('shipping_address');

            $table->boolean('is_export')
                ->default(false)
                ->after('shipping_custom_field_details');

            $table->string('export_custom_field_1')
                ->nullable()
                ->after('is_export');

            $table->string('export_custom_field_2')
                ->nullable()
                ->after('export_custom_field_1');

            $table->string('export_custom_field_3')
                ->nullable()
                ->after('export_custom_field_2');

            $table->string('export_custom_field_4')
                ->nullable()
                ->after('export_custom_field_3');

            $table->string('export_custom_field_5')
                ->nullable()
                ->after('export_custom_field_4');

            $table->string('export_custom_field_6')
                ->nullable()
                ->after('export_custom_field_5');
        });

        DB::statement("ALTER TABLE contacts MODIFY COLUMN name VARCHAR(191) DEFAULT NULL");
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
