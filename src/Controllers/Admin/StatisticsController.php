<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\PaymentItem;
use Azuriom\Support\Charts;
use Illuminate\Database\Eloquent\Builder;

class StatisticsController extends Controller
{
    public function index()
    {
        $gatewaysPayments = Gateway::all()->map(function (Gateway $gateway) {
            $query = $this->getCompletedPayments()
                ->where('gateway_type', '=', $gateway->type);
            $method =  payment_manager()->getPaymentMethod($gateway->type);

            return [
                'name' => $method ? $method->name() : $gateway->type,
                'color' => $method ? $method->color() : '#777',
                'totalByMonths' => Charts::sumByMonths($query, 'price'),
                'totalByDays' => Charts::sumByDays($query, 'price'),
            ];
        });

        return view('shop::admin.statistics', [
            'gatewaysPayments' => $gatewaysPayments,
            'monthPaymentsCount' => $this->getCompletedPayments()->where('created_at', '>', now()->startOfMonth())->count(),
            'monthPaymentsTotal' => $this->getCompletedPayments()->where('created_at', '>', now()->startOfMonth())->sum('price'),
            'paymentsCount' => $this->getCompletedPayments()->count(),
            'paymentsTotal' => $this->getCompletedPayments()->sum('price'),
            'paymentsPerMonths' => Charts::sumByMonths($this->getCompletedPayments(), 'price'),
            'paymentsPerDays' => Charts::sumByDays($this->getCompletedPayments(), 'price'),
            'gatewaysChart' => Charts::count($this->getCompletedPayments(), 'gateway_type'),
            'itemsChart' => Charts::count($this->getDeliveredPackages(), 'name'),
        ]);
    }

    protected function getCompletedPayments()
    {
        return Payment::scopes(['completed', 'withRealMoney']);
    }

    protected function getDeliveredPackages()
    {
        return PaymentItem::where('buyable_type', 'shop.packages')
            ->whereHas('payment', function (Builder $query) {
                $query->where('status', 'completed');
            });
    }
}
