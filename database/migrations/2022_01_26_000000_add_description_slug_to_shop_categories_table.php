<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (! Schema::hasColumn('shop_categories', 'slug')) {
            Schema::table('shop_categories', function (Blueprint $table) {
                $table->string('slug')->unique()->nullable()->after('name');
                $table->text('description')->nullable()->after('slug');
            });
        }

        foreach (DB::table('shop_categories')->get() as $cat) {
            DB::table('shop_categories')
                ->where('id', $cat->id)
                ->update(['slug' => $cat->id]);
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
