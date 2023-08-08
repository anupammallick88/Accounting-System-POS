<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatabaseChangesForRewardPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->boolean('enable_rp')->default(0)->after('created_by')->comment('rp is the short form of reward points');
            $table->string('rp_name')->nullable()->after('enable_rp')->comment('rp is the short form of reward points');
            $table->decimal('amount_for_unit_rp', 22, 4)->default(1)->after('rp_name')->comment('rp is the short form of reward points');
            $table->decimal('min_order_total_for_rp', 22, 4)->default(1)->after('amount_for_unit_rp')->comment('rp is the short form of reward points');
            $table->integer('max_rp_per_order')->nullable()->after('min_order_total_for_rp')->comment('rp is the short form of reward points');

            $table->decimal('redeem_amount_per_unit_rp', 22, 4)->default(1)->after('max_rp_per_order')->comment('rp is the short form of reward points');
            $table->decimal('min_order_total_for_redeem', 22, 4)->default(1)->after('redeem_amount_per_unit_rp')->comment('rp is the short form of reward points');
            $table->integer('min_redeem_point')->nullable()->after('min_order_total_for_redeem')->comment('rp is the short form of reward points');
            $table->integer('max_redeem_point')->nullable()->after('min_redeem_point')->comment('rp is the short form of reward points');
            $table->integer('rp_expiry_period')->nullable()->after('max_redeem_point')->comment('rp is the short form of reward points');
            $table->enum('rp_expiry_type', ['month', 'year'])->default('year')->after('rp_expiry_period')->comment('rp is the short form of reward points');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('rp_earned')->default(0)->after('created_by')->comment('rp is the short form of reward points');
            $table->integer('rp_redeemed')->default(0)->after('discount_amount')->comment('rp is the short form of reward points');
            $table->decimal('rp_redeemed_amount', 22, 4)->default(0)->after('rp_redeemed')->comment('rp is the short form of reward points');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->integer('total_rp')->default(0)->after('created_by')->comment('rp is the short form of reward points');
            $table->integer('total_rp_used')->default(0)->after('total_rp')->comment('rp is the short form of reward points');
            $table->integer('total_rp_expired')->default(0)->after('total_rp_used')->comment('rp is the short form of reward points');
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
