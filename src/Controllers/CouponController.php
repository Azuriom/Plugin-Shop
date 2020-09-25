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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function add(Request $request)
    {
        $validated = $this->validate($request, [
            'code' => ['required'],
        ]);

        $coupon = Coupon::active()->firstWhere($validated);

        if ($coupon === null || $coupon->hasReachLimit($request->user())) {
            throw ValidationException::withMessages([
                'code' => trans('shop::messages.cart.invalid-coupon'),
            ]);
        }

        Cart::fromSession($request->session())->addCoupon($coupon);

        return redirect()->route('shop.cart.index');
    }

    /**
     * Remove a coupon from the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request, Coupon $coupon)
    {
        Cart::fromSession($request->session())->removeCoupon($coupon);

        return redirect()->route('shop.cart.index');
    }

    /**
     * Clear the coupons in the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function clear(Request $request)
    {
        Cart::fromSession($request->session())->clearCoupon();

        return redirect()->route('shop.cart.index');
    }
}
