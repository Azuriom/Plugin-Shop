<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimitsColumnsToShopCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_coupons', function (Blueprint $table) {
            $table->unsignedInteger('user_limit')->nullable()->after('discount');
            $table->unsignedInteger('global_limit')->nullable()->after('user_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_coupons', function (Blueprint $table) {
            $table->dropColumn(['user_limit', 'global_limit']);
        });
    }
}
