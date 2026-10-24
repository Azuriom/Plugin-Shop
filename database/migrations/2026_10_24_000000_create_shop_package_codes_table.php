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
        Schema::create('shop_package_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('package_id')->nullable();
            $table->string('code')->unique();
            $table->unsignedInteger('payment_item_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('assigned_at')->nullable();

            $table->foreign('package_id')->references('id')->on('shop_packages')->nullOnDelete();
            $table->foreign('payment_item_id')->references('id')->on('shop_payment_items')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_package_codes');
    }
};
