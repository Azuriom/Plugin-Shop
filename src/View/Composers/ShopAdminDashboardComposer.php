<?php

namespace Azuriom\Plugin\Shop\View\Composers;

use Azuriom\Extensions\Plugin\AdminDashboardCardComposer;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Support\Facades\Gate;

class ShopAdminDashboardComposer extends AdminDashboardCardComposer
{
    public function getCards()
    {
        if (! Gate::allows('shop.admin')) {
            return [];
        }

        return [
            'shop_payments' => [
                'color' => 'info',
                'name' => trans('shop::admin.payments.card'),
                'value' => Payment::scopes(['completed', 'withRealMoney'])->count(),
                'icon' => 'bi bi-cash',
            ],
        ];
    }
}
