<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopGiftcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_giftcards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->unsignedDecimal('amount');
            $table->unsignedInteger('global_limit')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_giftcard_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('giftcard_id');
            $table->unsignedInteger('user_id');

            $table->foreign('giftcard_id')->references('id')->on('shop_giftcards')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_giftcards');
        Schema::dropIfExists('shop_giftcard_user');
    }
}
