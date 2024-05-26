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
        Schema::create('shop_discounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('discount');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_global')->default(false);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_discount_package', function (Blueprint $table) {
            $table->unsignedInteger('discount_id');
            $table->unsignedInteger('package_id');

            $table->foreign('discount_id')->references('id')->on('shop_discounts')->cascadeOnDelete();
            $table->foreign('package_id')->references('id')->on('shop_packages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_discounts');
        Schema::dropIfExists('shop_discount_package');
    }
};
