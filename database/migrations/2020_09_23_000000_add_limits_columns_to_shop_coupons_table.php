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
        Schema::table('shop_coupons', function (Blueprint $table) {
            $table->unsignedInteger('user_limit')->nullable()->after('discount');
            $table->unsignedInteger('global_limit')->nullable()->after('user_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_coupons', function (Blueprint $table) {
            $table->dropColumn(['user_limit', 'global_limit']);
        });
    }
};
