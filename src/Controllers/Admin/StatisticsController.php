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

        $perGateway = [];

        foreach (Gateway::all() as $gateway) {
            $query = $this->getCompletedPayments()->where('gateway_type', '=', $gateway->type);
            $perGateway[] = [
                'paymentsCountPerMonths' => Charts::countByMonths($query),
                'paymentsPerMonths' => Charts::sumByMonths($query, 'price'),
                'name' => $gateway->type
            ];
        }

        return view('shop::admin.statistics', [
            'perGateway' => $perGateway,
            'monthPaymentsCount' => $this->getCompletedPayments()->where('created_at', '>', now()->startOfMonth())->count(),
            'monthPaymentsTotal' => $this->getCompletedPayments()->where('created_at', '>', now()->startOfMonth())->sum('price'),
            'paymentsCount' => $this->getCompletedPayments()->count(),
            'paymentsTotal' => $this->getCompletedPayments()->sum('price'),
            'paymentsPerMonths' => Charts::sumByMonths($this->getCompletedPayments(), 'price'),
            'paymentsPerDays' => Charts::sumByDays($this->getCompletedPayments(), 'price'),
            'paymentsCountPerMonths' => Charts::countByMonths($this->getCompletedPayments()),
            'paymentsCountPerDays' => Charts::countByMonths($this->getCompletedPayments()),
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
