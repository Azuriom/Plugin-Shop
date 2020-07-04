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
            'payments' => $this->getPaymentsMonth(),
            'estimated' => $this->getEstimatedEarnings(),
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

        for ($i = 0; $i < 30; $i++) {
            $date->addDay();
            $time = $date->translatedFormat('l j F Y');
            $payments[$time] = $queryPayments->get($time, 0);
        }

        return collect($payments);
    }

}
