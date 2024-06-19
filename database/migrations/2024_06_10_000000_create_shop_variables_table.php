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
        Schema::create('shop_variables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->string('type');
            $table->text('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        Schema::create('shop_package_variable', function (Blueprint $table) {
            $table->unsignedInteger('package_id');
            $table->unsignedInteger('variable_id');

            $table->foreign('package_id')->references('id')->on('shop_packages')->cascadeOnDelete();
            $table->foreign('variable_id')->references('id')->on('shop_variables')->cascadeOnDelete();
        });

        Schema::table('shop_payment_items', function (Blueprint $table) {
            $table->text('variables')->nullable()->after('buyable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_variables');
        Schema::dropIfExists('shop_package_variable');
    }
};
