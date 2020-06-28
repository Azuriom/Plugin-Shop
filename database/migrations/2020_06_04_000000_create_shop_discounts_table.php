<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopDiscountsTable extends Migration
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
        Schema::dropIfExists('shop_discounts');
        Schema::dropIfExists('shop_discount_package');

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

            $table->foreign('discount_id')->references('id')->on('shop_discounts')->onDelete('cascade');
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
        Schema::dropIfExists('shop_discounts');
        Schema::dropIfExists('shop_discount_package');
    }
}
