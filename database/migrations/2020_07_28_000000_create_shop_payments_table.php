<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateShopPaymentsTable extends Migration
{
    private $oldStatusMap = [
        'CREATED' => 'pending',
        'PENDING' => 'pending',
        'CANCELLED' => 'expired',
        'EXPIRED' => 'expired',
        'SUCCESS' => 'completed',
        'DELIVERED' => 'completed',
        'ERROR' => 'error',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // TODO Azuriom 1.0: remove old migration
        if (Schema::hasTable('shop_purchases')) {
            $this->backupOldPayments();
        }

        Schema::create('shop_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedDecimal('price');
            $table->char('currency', 3);
            $table->string('gateway_type');
            $table->string('status'); // pending, expired, completed, error, chargeback, refund
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_payments');
    }

    /**
     * Backup old payments/purchases to a local .json file before deleting tables.
     *
     * @return void
     */
    protected function backupOldPayments()
    {
        $packages = DB::table('shop_packages')->get()->keyBy('id');
        $offers = DB::table('shop_offers')->get()->keyBy('id');

        $oldPurchases = DB::table('shop_purchases')->get()->map(function ($purchase) use ($packages) {
            $item = $packages->get($purchase->package_id);

            return [
                'user_id' => $purchase->user_id,
                'price' => $purchase->price,
                'currency' => 'XXX',
                'gateway_type' => 'azuriom',
                'status' => 'completed',
                'created_at' => $purchase->created_at,
                'updated_at' => $purchase->updated_at,
                'items' => [
                    [
                        'name' => $item ? $item->name : '?',
                        'price' => $purchase->price,
                        'quantity' => $purchase->quantity,
                        'buyable_id' => $purchase->package_id,
                        'buyable_type' => 'shop.packages',
                    ],
                ],
            ];
        });

        $oldPayments = DB::table('shop_payments')
            ->where('type', '!=', 'CREATED')
            ->orWhere('created_at', '>', now()->subDay())
            ->get()
            ->map(function ($payment) use ($packages, $offers) {
                $items = collect(json_decode($payment->items) ?? [])
                    ->filter(function ($quantity, $itemId) {
                        return is_numeric($quantity) && is_numeric($itemId);
                    })
                    ->map(function ($quantity, $itemId) use ($payment, $packages, $offers) {
                        $buyable = ($payment->type === 'PACKAGE' ? $packages : $offers)->get($itemId);

                        return [
                            'name' => $buyable->name ?? '?',
                            'price' => $buyable->price ?? 0,
                            'quantity' => $quantity,
                            'buyable_id' => $itemId,
                            'buyable_type' => $payment->type === 'PACKAGE' ? 'shop.packages' : 'shop.offers',
                        ];
                    });

                return [
                    'user_id' => $payment->user_id,
                    'price' => $payment->price,
                    'currency' => $payment->currency,
                    'gateway_type' => $payment->payment_type,
                    'status' => Arr::get($this->oldStatusMap, $payment->status, 'error'),
                    'transaction_id' => $payment->payment_id,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                    'items' => $items->all(),
                ];
            })->merge($oldPurchases)->sortBy('created_at')->values();

        $json = json_encode($oldPayments->all(), JSON_PRETTY_PRINT);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON error: '.json_last_error_msg());
        }

        file_put_contents(storage_path('shop_backup.json'), $json);

        Schema::dropIfExists('shop_payments');
        Schema::dropIfExists('shop_purchases');
    }
}
