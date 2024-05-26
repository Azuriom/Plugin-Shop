<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Package;
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
                ->where('gateway_type', $gateway->type);

            return [
                'name' => $gateway->getTypeName(),
                'totalByMonths' => Charts::sumByMonths($query, 'price'),
                'totalByDays' => Charts::sumByDays($query, 'price'),
            ];
        });
        $packagesCounts = Charts::count($this->getDeliveredPackages(), 'buyable_id');
        $packagesTotals = Charts::sum($this->getDeliveredPackages(), 'price', 'buyable_id');

        $packages = Package::all()->map(fn (Package $package) => $package->forceFill([
            'count' => $packagesCounts->get($package->id, 0),
            'total' => $packagesTotals->get($package->id, 0),
        ]))->sortByDesc('count');

        return view('shop::admin.statistics.index', [
            'gatewaysPayments' => $gatewaysPayments,
            'monthPaymentsCount' => $this->getCompletedPayments()->where('created_at', '>', now()->startOfMonth())->count(),
            'monthPaymentsTotal' => $this->getCompletedPayments()->where('created_at', '>', now()->startOfMonth())->sum('price'),
            'paymentsCount' => $this->getCompletedPayments()->count(),
            'paymentsTotal' => $this->getCompletedPayments()->sum('price'),
            'paymentsPerMonths' => Charts::sumByMonths($this->getCompletedPayments(), 'price'),
            'paymentsPerDays' => Charts::sumByDays($this->getCompletedPayments(), 'price'),
            'gatewaysChart' => Charts::count($this->getCompletedPayments(), 'gateway_type'),
            'packages' => $packages,
        ]);
    }

    public function showPackage(Package $package)
    {
        $query = $this->getDeliveredPackages()->where('buyable_id', $package->id);
        $byGateway = $this->getCompletedPayments()
            ->whereHas('items', function (Builder $query) use ($package) {
                $query->whereMorphedTo('buyable', $package);
            });

        return view('shop::admin.statistics.package', [
            'package' => $package,
            'paymentsPerMonths' => Charts::sumByMonths($query->clone(), 'price'),
            'paymentsPerDays' => Charts::sumByDays($query, 'price'),
            'gatewaysChart' => Charts::count($byGateway, 'gateway_type'),
        ]);
    }

    protected function getCompletedPayments(): Builder
    {
        return Payment::scopes(['completed', 'withRealMoney']);
    }

    protected function getDeliveredPackages(): Builder
    {
        return PaymentItem::where('buyable_type', 'shop.packages')
            ->whereHas('payment', function (Builder $query) {
                $query->where('status', 'completed');
            });
    }
}
