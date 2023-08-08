<?php

use App\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCategoriesTableForPolymerphicRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('category_type')->nullable()->after('created_by');
            $table->text('description')->nullable()->after('category_type');
            $table->string('slug')->nullable()->after('description');
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->integer('category_id');
            $table->morphs('categorizable');
        });

        Category::whereNotNull('id')->update(['category_type' => 'product']);
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
