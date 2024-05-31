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
            $table->string('billing_type')->default('one-off')->after('price'); // one-off, expiring, subscription
            $table->string('billing_period')->nullable()->after('billing_type');
            $table->boolean('limits_no_expired')->default(false)->after('global_limit_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_packages', function (Blueprint $table) {
            $table->dropColumn(['billing_type', 'billing_period', 'limits_no_expired']);
        });
    }
};
