<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\PaymentItem;
use Azuriom\Support\Charts;
use Illuminate\Database\Eloquent\Builder;

class StatisticsController extends Controller
{
    public function index()
    {
        return view('shop::admin.statistics', [
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
