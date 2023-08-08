<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFieldsForComboProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('variations', function (Blueprint $table) {
            $table->text('combo_variations')->nullable()->comment('Contains the combo variation details');
        });

        DB::statement("ALTER TABLE `products` CHANGE `type` `type` ENUM('single','variable','modifier','combo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");

        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            $table->string('children_type')
                ->default('')
                ->after('parent_sell_line_id')
                ->comment('Type of children for the parent, like modifier or combo');

            $table->index(['children_type']);
            $table->index(['parent_sell_line_id']);
        });

        DB::statement("UPDATE transaction_sell_lines SET children_type='modifier' WHERE parent_sell_line_id IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variations', function (Blueprint $table) {
            $table->dropColumn(['combo_variations']);
        });
    }
}
