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
        Schema::table('shop_payments', function (Blueprint $table) {
            $table->unsignedInteger('subscription_id')->nullable()->after('user_id');

            $table->foreign('subscription_id')->references('id')->on('shop_subscriptions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_packages', function (Blueprint $table) {
            $table->dropColumn(['billing_type', 'billing_period']);
        });
    }
};
