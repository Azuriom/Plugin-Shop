<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('price');
            $table->unsignedInteger('money');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('shop_offer_gateways', function (Blueprint $table) {
            $table->unsignedInteger('offer_id');
            $table->unsignedInteger('gateway_id');

            $table->foreign('offer_id')->references('id')->on('shop_offers')->cascadeOnDelete();
            $table->foreign('gateway_id')->references('id')->on('shop_gateways')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_offers');
        Schema::dropIfExists('shop_offer_gateways');
    }
};
