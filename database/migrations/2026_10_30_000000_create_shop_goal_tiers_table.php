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
        Schema::create('shop_goal_tiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->decimal('amount');
            $table->text('commands')->nullable();
            $table->decimal('money')->nullable();
            $table->decimal('giftcard_balance')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_goal_completions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tier_id')->nullable();
            $table->string('month');
            $table->decimal('total');
            $table->unsignedInteger('participants');
            $table->timestamp('created_at')->nullable();

            $table->foreign('tier_id')->references('id')->on('shop_goal_tiers')->nullOnDelete();

            $table->unique(['tier_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_goal_completions');
        Schema::dropIfExists('shop_goal_tiers');
    }
};
