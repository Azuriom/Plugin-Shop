<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_packages', function (Blueprint $table) {
            $table->unsignedDecimal('giftcard_balance')->nullable()->after('money');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_packages', function (Blueprint $table) {
            $table->dropColumn('giftcard_balance');
        });
    }
};
