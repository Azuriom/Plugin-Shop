<?php

namespace Azuriom\Plugin\Shop\View\Composers;

use Azuriom\Extensions\Plugin\AdminDashboardCardComposer;
use Azuriom\Plugin\Shop\Models\Payment;

class ShopAdminDashboardComposer extends AdminDashboardCardComposer
{
    public function getCards()
    {
        return [
            'shop_payments' => [
                'color' => 'info',
                'name' => trans('shop::admin.payments.card'),
                'value' => Payment::scopes(['completed', 'withRealMoney'])->count(),
                'icon' => 'fas fa-money-bill-wave',
            ],
        ];
    }
}
