<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedDecimal('price');
            $table->char('currency', 3);
            $table->string('payment_type');
            $table->enum('status', ['CREATED', 'CANCELLED', 'PENDING', 'EXPIRED', 'SUCCESS', 'DELIVERED', 'ERROR']);
            $table->string('type');
            $table->string('payment_id', 300)->nullable();
            $table->text('items');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_payments');
    }
}
