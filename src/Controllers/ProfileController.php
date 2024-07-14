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
        $payments = Payment::whereBelongsTo($user)
            ->scopes(['notPending', 'withRealMoney'])
            ->latest()
            ->get();

        $subscriptions = Subscription::notPending()
            ->whereBelongsTo($user)
            ->with('package')
            ->latest()
            ->get();

        return view('shop::profile.index', [
            'user' => $user,
            'payments' => $payments,
            'subscriptions' => $subscriptions,
            'giftCardCode' => $request->query('giftcard'),
        ]);
    }
}
