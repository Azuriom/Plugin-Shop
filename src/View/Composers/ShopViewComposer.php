<?php

namespace Azuriom\Plugin\Shop\View\Composers;

use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ShopViewComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Route::is('admin.*')) {
            return;
        }

        $user = shop_user();

        $view->with([
            'shopUser' => shop_user(),
            'guestShopLogin' => ! use_site_money() && setting('shop.guest_purchases', false),
            'userHasPayments' => $user !== null && Payment::whereBelongsTo($user)->exists(),
        ]);
    }
}
