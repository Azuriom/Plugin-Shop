<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('name');
            $table->string('short_description');
            $table->text('description');
            $table->unsignedInteger('position')->default(0);
            $table->string('image')->nullable();
            $table->unsignedDecimal('price');
            $table->text('commands');
            $table->boolean('need_online')->default(false);
            $table->unsignedInteger('user_limit')->nullable();
            $table->text('required_packages')->nullable();
            $table->boolean('has_quantity')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('shop_categories');
        });

        Schema::create('shop_package_server', function (Blueprint $table) {
            $table->unsignedInteger('package_id');
            $table->unsignedInteger('server_id');

            $table->foreign('package_id')->references('id')->on('shop_packages')->onDelete('cascade');
            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_packages');
        Schema::dropIfExists('shop_package_server');
    }
}
