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
        Schema::create('shop_tiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('min_spend', 10, 2);
            $table->string('icon')->nullable();
            $table->string('type');
            $table->json('reward_data')->nullable();
            $table->json('commands')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('shop_tier_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tier_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('unlocked_at');
            $table->json('reward_details')->nullable();
            $table->timestamps();

            $table->foreign('tier_id')->references('id')->on('shop_tiers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['tier_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_tier_user');
        Schema::dropIfExists('shop_tiers');
    }
};
