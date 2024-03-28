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
        Schema::table('shop_packages', function (Blueprint $table) {
            $table->string('user_limit_period')->nullable()->after('user_limit');
            $table->string('global_limit_period')->nullable()->after('global_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_packages', function (Blueprint $table) {
            $table->dropColumn(['user_limit_period', 'global_limit_period']);
        });
    }
};
