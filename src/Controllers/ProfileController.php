<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Payment;
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
            ->get();

        return view('shop::profile.index', [
            'user' => $user,
            'payments' => $payments,
            'giftCardCode' => $this->getGiftCardCodeFromUrl($request),
        ]);
    }

    private function getGiftCardCodeFromUrl(Request $request): ?string
    {
        $giftCardCode = $request->query('giftcard', null);

        if ($giftCardCode === null) {
            return null;
        }

        if (is_array($giftCardCode)) {
            return null;
        }

        return $giftCardCode;
    }
}
