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
        Schema::create('shop_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->decimal('price');
            $table->char('currency', 3);
            $table->string('gateway_type');
            $table->string('status'); // pending, expired, completed, error, chargeback, refunded
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('shop_coupon_payment', function (Blueprint $table) {
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('coupon_id');

            $table->foreign('payment_id')->references('id')->on('shop_payments')->cascadeOnDelete();
            $table->foreign('coupon_id')->references('id')->on('shop_coupons')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_payments');
    }
};
