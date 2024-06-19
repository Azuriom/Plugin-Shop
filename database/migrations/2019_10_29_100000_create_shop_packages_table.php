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
        Schema::create('shop_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('name');
            $table->string('short_description');
            $table->text('description');
            $table->unsignedInteger('position')->default(0);
            $table->string('image')->nullable();
            $table->decimal('price');
            $table->text('commands');
            $table->unsignedInteger('role_id')->nullable();
            $table->text('required_roles')->nullable();
            $table->unsignedInteger('user_limit')->nullable();
            $table->text('required_packages')->nullable();
            $table->boolean('has_quantity')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('shop_categories');
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_packages');
    }
};
