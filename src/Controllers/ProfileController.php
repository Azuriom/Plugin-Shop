<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\Subscription;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $payments = collect();
        $paymentsWithSiteMoney = collect();

        if (use_site_money()) {
            $paymentsWithSiteMoney = Payment::whereBelongsTo($user)
                ->scopes(['notPending', 'withSiteMoney'])
                ->latest()
                ->get();
        } else {
            $payments = Payment::whereBelongsTo($user)
                ->scopes(['notPending', 'withRealMoney'])
                ->latest()
                ->get();
        }

        $subscriptions = Subscription::notPending()
            ->whereBelongsTo($user)
            ->with('package')
            ->latest()
            ->get();

        return view('shop::profile.index', [
            'user' => $user,
            'payments' => $payments,
            'paymentsWithSiteMoney' => $paymentsWithSiteMoney,
            'subscriptions' => $subscriptions,
            'giftCardCode' => null, // TODO remove unused variable, kept for compatibility
        ]);
    }
}
