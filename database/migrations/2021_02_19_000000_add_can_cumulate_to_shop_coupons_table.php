<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanCumulateToShopCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_coupons', function (Blueprint $table) {
            $table->boolean('can_cumulate')->default(true)->after('global_limit');
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
            $table->dropColumn('can_cumulate');
        });
    }
}
