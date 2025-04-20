<?php

namespace Azuriom\Plugin\Shop\View\Composers;

use Azuriom\Extensions\Plugin\AdminUserEditComposer;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Illuminate\View\View;

class ShopAdminUserComposer extends AdminUserEditComposer
{
    public function getCards(User $user, View $view)
    {
        $payments = Payment::whereBelongsTo($user)
            ->scopes(['completed', 'withRealMoney'])
            ->get();

        if ($payments->isEmpty()) {
            return [];
        }

        $view->with('payments', $payments);
        $view->with('total', Currencies::formatAmount($payments->sum('price'), currency()));

        return [
            'shop' => [
                'name' => trans('shop::admin.payments.title'),
                'view' => 'shop::admin.users.payments',
            ],
        ];
    }
}
