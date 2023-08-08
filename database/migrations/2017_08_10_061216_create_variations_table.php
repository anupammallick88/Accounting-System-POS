<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('sub_sku')->nullable();
            $table->integer('product_variation_id')->unsigned();
            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('cascade');
            $table->decimal('default_purchase_price', 22, 4)->nullable();
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->nullable();
            $table->decimal('sell_price_inc_tax', 22, 4)->comment("Sell price including tax")->nullable();
            $table->timestamps();
            $table->softDeletes();

            //Indexing
            $table->index('name');
            $table->index('sub_sku');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variations');
    }
}
