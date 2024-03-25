<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('shop_packages')
            ->where('giftcard_balance', 0)
            ->update(['giftcard_balance' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ignore
    }
};
