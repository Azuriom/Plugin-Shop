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
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->boolean('single_purchase')->default(false)->after('cumulate_purchases');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->dropColumn('single_purchase');
        });
    }
};
