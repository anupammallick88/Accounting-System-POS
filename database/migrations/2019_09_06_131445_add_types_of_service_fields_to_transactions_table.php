<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypesOfServiceFieldsToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('types_of_service_id')->nullable()->after('created_by');
            $table->decimal('packing_charge', 22, 4)->nullable()->after('types_of_service_id');
            $table->enum('packing_charge_type', ['fixed', 'percent'])->nullable()->after('packing_charge');
            $table->text('service_custom_field_1')->nullable()->after('packing_charge_type');
            $table->text('service_custom_field_2')->nullable()->after('service_custom_field_1');
            $table->text('service_custom_field_3')->nullable()->after('service_custom_field_2');
            $table->text('service_custom_field_4')->nullable()->after('service_custom_field_3');
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
