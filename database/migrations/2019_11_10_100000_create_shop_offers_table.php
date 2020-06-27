<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedDecimal('price');
            $table->unsignedInteger('money');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('shop_offer_gateways', function (Blueprint $table) {
            $table->unsignedInteger('offer_id');
            $table->unsignedInteger('gateway_id');

            $table->foreign('offer_id')->references('id')->on('shop_offers')->onDelete('cascade');
            $table->foreign('gateway_id')->references('id')->on('shop_gateways')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_offers');

        Schema::dropIfExists('shop_offer_gateways');
    }
}
