<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateShopPaymentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_payment_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id');
            $table->string('name');
            $table->unsignedDecimal('price');
            $table->unsignedInteger('quantity');
            $table->morphs('buyable'); // offer or package
            $table->timestamps();

            $table->foreign('payment_id')
                ->references('id')
                ->on('shop_payments')
                ->onDelete('cascade');
        });

        if (file_exists($path = storage_path('shop_backup.json'))) {
            $this->restoreOldPayments($path);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_payment_items');
    }

    protected function restoreOldPayments(string $path)
    {
        @set_time_limit(180); // 3 minutes

        $payments = json_decode(file_get_contents($path), true);

        foreach ($payments as $payment) {
            $paymentID = DB::table('shop_payments')->insertGetId(Arr::except($payment, 'items'));

            foreach ($payment['items'] ?? [] as $item) {
                if (! is_numeric($item['quantity']) || ! is_numeric($item['buyable_id'])) {
                    continue;
                }

                DB::table('shop_payment_items')->insert(array_merge($item, [
                    'payment_id' => $paymentID,
                    'created_at' => $payment['created_at'],
                    'updated_at' => $payment['updated_at'],
                ]));
            }
        }
    }
}
