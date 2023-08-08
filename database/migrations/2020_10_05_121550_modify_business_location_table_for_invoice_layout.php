<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\BusinessLocation;
use App\InvoiceLayout;
use Illuminate\Support\Facades\DB;

class ModifyBusinessLocationTableForInvoiceLayout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_locations', function (Blueprint $table) {
            $table->integer('sale_invoice_layout_id')->nullable()->after('invoice_layout_id');
        });

        BusinessLocation::whereNotNull('id')->update([
            "sale_invoice_layout_id" => DB::raw("invoice_layout_id"),
        ]);
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
