<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix for a wrong shop updates
        // TODO Azuriom 1.0 remove
        Schema::dropIfExists('shop_coupons');
        Schema::dropIfExists('shop_coupon_package');

        Schema::create('shop_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->unsignedInteger('discount');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_global')->default(false);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_coupon_package', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id');
            $table->unsignedInteger('package_id');

            $table->foreign('coupon_id')->references('id')->on('shop_coupons')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('shop_packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_coupons');
        Schema::dropIfExists('shop_coupon_package');
    }
}
