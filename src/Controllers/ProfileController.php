<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\PackageCode;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
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
        $purchases = Payment::whereBelongsTo($user)
            ->withSiteMoney()
            ->latest()
            ->get();

        $subscriptions = Subscription::notPending()
            ->whereBelongsTo($user)
            ->with('package')
            ->latest()
            ->get();

        $codes = PackageCode::whereHas('paymentItem.payment', fn (Builder $q) => $q->whereBelongsTo($user))
            ->with('paymentItem')
            ->latest('assigned_at')
            ->get();

        return view('shop::profile.index', [
            'user' => $user,
            'payments' => $payments,
            'purchases' => $purchases,
            'subscriptions' => $subscriptions,
            'codes' => $codes,
            'giftCardCode' => null, // TODO remove unused variable, kept for compatibility
        ]);
    }
}
