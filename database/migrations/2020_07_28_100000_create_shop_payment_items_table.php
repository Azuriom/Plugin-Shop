<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_payment_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id');
            $table->string('name');
            $table->decimal('price');
            $table->unsignedInteger('quantity');
            $table->morphs('buyable'); // offer or package
            $table->timestamps();

            $table->foreign('payment_id')
                ->references('id')
                ->on('shop_payments')
                ->cascadeOnDelete();
        });

        if (file_exists($path = storage_path('shop_backup.json'))) {
            $this->restoreOldPayments($path);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_payment_items');
    }

    protected function restoreOldPayments(string $path)
    {
        @set_time_limit(180); // 3 minutes

        $content = file_get_contents($path);
        $payments = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

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
};
