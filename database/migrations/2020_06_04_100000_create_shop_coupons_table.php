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
        Schema::create('shop_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->unsignedInteger('discount');
            $table->boolean('can_cumulate')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_global')->default(false);
            $table->boolean('is_fixed')->default(false);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_coupon_package', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id');
            $table->unsignedInteger('package_id');

            $table->foreign('coupon_id')->references('id')->on('shop_coupons')->cascadeOnDelete();
            $table->foreign('package_id')->references('id')->on('shop_packages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_coupons');
        Schema::dropIfExists('shop_coupon_package');
        Schema::dropIfExists('shop_coupon_payment');
    }
};
