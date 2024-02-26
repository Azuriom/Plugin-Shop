<?php

declare(strict_types=1);

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
        Schema::table('shop_coupons', function (Blueprint $table) {
            $table->boolean('discount_allowed')->default(true)->after('can_cumulate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_coupons', function (Blueprint $table) {
            $table->dropColumn('discount_allowed');
        });
    }
};
