<?php


namespace Azuriom\Plugin\Shop\Controllers\Admin;


use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Vote\Models\Vote;

class StatisticsController
{

    public function index()
    {
        return view("shop::admin.statistics", [
            'payment' => Payment::completed()->count(),
            'payment_month' => $this->getPaymentMonth(),
            'payments' => $this->getPaymentsMonth(),
            'estimated' => $this->getEstimatedEarnings(),
            'estimated_month' => $this->getEstimatedEarningsMonth(),
            'payments_estimated' => $this->getPaymentsCountMonth(),
        ]);
    }

    /**
     * @return mixed
     */
    private function getEstimatedEarnings()
    {
        return Payment::completed()->sum('price');
    }

    /**
     * @return mixed
     */
    private function getEstimatedEarningsMonth()
    {
        $date = now()->startOfMonth();
        return Payment::completed()->where('created_at', '>=', $date)->sum('price');
    }
    /**
     * @return mixed
     */
    private function getPaymentMonth()
    {
        $date = now()->startOfMonth();
        return Payment::completed()->where('created_at', '>=', $date)->count();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getPaymentsMonth()
    {

        $date = now()->subMonths(1);
        $payments = [];

        $queryPayments = Payment::completed()->whereDate('created_at', '>=', $date)
            ->get(['id', 'created_at'])
            ->countBy(function ($payment) {
                return $payment->created_at->translatedFormat('l j F Y');
            });

        for ($i = 0; $i < 31; $i++) {
            $time = $date->translatedFormat('l j F Y');
            $payments[$time] = $queryPayments->get($time, 0);
            $date->addDay();
        }

        return collect($payments);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getPaymentsCountMonth()
    {

        $date = now()->subMonths(1);
        $payments = [];

        for ($i = 0; $i < 31; $i++) {
            $time = $date->translatedFormat('l j F Y');
            $payments[$time] = Payment::completed()->whereDate('created_at', '=', $date)->sum('price');
            $date->addDay();
        }

        return collect($payments);
    }

}
