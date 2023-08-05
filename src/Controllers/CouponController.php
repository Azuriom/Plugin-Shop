<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    /**
     * Add a coupon to the cart.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function add(Request $request)
    {
        $validated = $this->validate($request, ['code' => 'required']);

        $coupon = Coupon::active()->firstWhere($validated);

        if ($coupon === null || $coupon->hasReachLimit($request->user())) {
            throw ValidationException::withMessages([
                'code' => trans('shop::messages.coupons.error'),
            ]);
        }

        $cart = Cart::fromSession($request->session());

        if ((! $coupon->can_cumulate && ! $cart->coupons()->isEmpty())
            || $cart->coupons()->contains('can_cumulate', false)) {
            throw ValidationException::withMessages([
                'code' => trans('shop::messages.coupons.cumulate'),
            ]);
        }

        $cart->addCoupon($coupon);

        return to_route('shop.cart.index');
    }

    /**
     * Remove a coupon from the cart.
     */
    public function remove(Request $request, Coupon $coupon)
    {
        Cart::fromSession($request->session())->removeCoupon($coupon);

        return to_route('shop.cart.index');
    }

    /**
     * Clear the coupons in the cart.
     */
    public function clear(Request $request)
    {
        Cart::fromSession($request->session())->clearCoupons();

        return to_route('shop.cart.index');
    }
}
