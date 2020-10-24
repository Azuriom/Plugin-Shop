<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Support\Charts;

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
        ]);
    }

    protected function getCompletedPayments()
    {
        return Payment::scopes(['completed', 'withRealMoney']);
    }
}
