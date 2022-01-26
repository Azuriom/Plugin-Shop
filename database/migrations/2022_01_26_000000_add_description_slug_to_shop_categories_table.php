<?php

use Azuriom\Plugin\Shop\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionSlugToShopCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->text('description')->nullable()->after('slug');
        });

        foreach (Category::all() as $category) {
            $category->update(['slug' => $category->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('description');
        });
    }
}
