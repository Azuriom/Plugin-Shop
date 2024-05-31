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
        Schema::create('shop_gateway_metadata', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('gateway_id');
            $table->morphs('model');
            $table->string('value');
            $table->timestamps();

            $table->foreign('gateway_id')->references('id')->on('shop_gateways')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_gateway_metadata');
    }
};
