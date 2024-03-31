<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Giftcard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GiftcardController extends Controller
{
    /**
     * Add a giftcard to the cart.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function add(Request $request)
    {
        $validated = $this->validate($request, ['code' => 'required']);

        $giftcard = Giftcard::firstWhere($validated);

        if ($giftcard !== null && $giftcard->isPending()) {
            throw ValidationException::withMessages([
                'code' => trans('shop::messages.giftcards.pending'),
            ]);
        }

        $giftcard?->refreshBalance();

        if ($giftcard === null || ! $giftcard->isActive()) {
            throw ValidationException::withMessages([
                'code' => trans('shop::messages.giftcards.error'),
            ]);
        }

        Cart::fromSession($request->session())->addGiftcard($giftcard);

        return to_route('shop.cart.index');
    }

    /**
     * Remove a giftcard from the cart.
     */
    public function remove(Request $request, Giftcard $giftcard)
    {
        Cart::fromSession($request->session())->removeGiftcard($giftcard);

        return to_route('shop.cart.index');
    }
}
