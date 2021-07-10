<?php

use Illuminate\Support\Facades\DB;
use Azuriom\Plugin\Shop\Models\Offer;
use Illuminate\Support\Facades\Schema;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Category;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwitchToSpatieTranslationsShop extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('shop_offers', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('shop_packages', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('short_description')->change();
        });

        $locale = App::getLocale();

        $rawModels = DB::table('shop_categories')->get();
        foreach ($rawModels as $key => $category) {
            $category = Category::find($category->id);
            $category
                ->setTranslation('name', $locale, $rawModels[$key]->name)
                ->save();
        }

        $rawModels = DB::table('shop_offers')->get();
        foreach ($rawModels as $key => $offer) {
            $offer = Offer::find($offer->id);
            $offer
                ->setTranslation('name', $locale, $rawModels[$key]->name)
                ->save();
        }

        $rawModels = DB::table('shop_packages')->get();
        foreach ($rawModels as $key => $package) {
            $package = Package::find($package->id);
            $package
                ->setTranslation('name', $locale, $rawModels[$key]->name)
                ->setTranslation('short_description', $locale, $rawModels[$key]->short_description)
                ->setTranslation('description', $locale, $rawModels[$key]->description)
                ->save();
        }
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
