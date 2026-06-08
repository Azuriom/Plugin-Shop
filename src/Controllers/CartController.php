<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Support\Markdown;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Display the user cart.
     */
    public function index(Request $request)
    {
        $terms = setting('shop.required_terms');

        if ($terms !== null) {
            $markdown = Markdown::parse($terms, true);

            $terms = new HtmlString(Str::between($markdown, '<p>', '</p>'));
        }

        $cart = Cart::fromSession($request->session());

        $userBalance = (auth()->check() && use_site_money()) ? auth()->user()->money : 0;

        return view('shop::cart.index', [
            'cart' => $cart,
            'terms' => $terms,
            'emailRequired' => auth()->check() && auth()->user()->email === null,
            'userBalance' => $userBalance,
        ]);
    }

    /**
     * Remove a package from the cart.
     */
    public function remove(Request $request, Package $package)
    {
        $cart = Cart::fromSession($request->session());

        $cart->remove($package);

        return to_route('shop.cart.index');
    }

    /**
     * Update the quantity of the items in the cart.
     */
    public function update(Request $request)
    {
        $cart = Cart::fromSession($request->session());

        foreach ($request->input('quantities', []) as $id => $quantity) {
            $item = $cart->getById($id);

            if ($item !== null && $quantity > 0) {
                $item->setQuantity($quantity);
            }

            $cart->save();
        }

        return to_route('shop.cart.index');
    }

    /**
     * Clear the user cart.
     */
    public function clear(Request $request)
    {
        Cart::fromSession($request->session())->clear();

        return to_route('shop.cart.index');
    }

    /**
     * Store the site balance amount the user wants to spend (AJAX).
     *
     * The value is clamped server-side to [0, min(userBalance, cartTotal)]
     * so the client cannot exceed what they actually own.
     */
    public function setBalance(Request $request)
    {
        if (! use_site_money()) {
            return response()->json(['error' => 'Site money is not enabled.'], 403);
        }

        $request->validate(['amount' => ['required', 'numeric', 'min:0']]);

        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return response()->json(['error' => 'Cart is empty.'], 422);
        }

        $requested = (float) $request->input('amount');
        $clamped = min($requested, $request->user()->money, $cart->payableTotal());

        $cart->setBalanceAmount($clamped);

        return response()->json([
            'balance_amount' => $clamped,
            'gateway_total' => round($cart->gatewayTotal(), 2),
            'cart_total' => round($cart->payableTotal(), 2),
        ]);
    }

    /**
     * Process cart payment.
     *
     * When site money is enabled:
     *   - Full balance coverage  → deduct balance atomically and complete immediately.
     *   - Partial coverage       → store pending balance in session, redirect to gateway.
     *   - No balance selected    → redirect to gateway for the full amount.
     *
     * When site money is disabled the user is redirected to the payment gateway directly.
     */
    public function payment(Request $request)
    {
        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return to_route('shop.cart.index');
        }

        if (! use_site_money()) {
            return to_route('shop.payments.payment');
        }

        $balanceToUse = $cart->getBalanceAmount();
        $total = $cart->payableTotal();

        // Full payment from balance — use a DB transaction with a pessimistic
        // lock to prevent race conditions across concurrent sessions.
        if ($balanceToUse >= $total) {
            try {
                DB::transaction(function () use ($request, $cart, $total) {
                    $user = $request->user()
                        ->newQuery()
                        ->lockForUpdate()
                        ->findOrFail($request->user()->id);

                    if (! $user->hasMoney($total)) {
                        throw new \RuntimeException('insufficient_balance');
                    }

                    $user->removeMoney($total);

                    payment_manager()->buyPackages($cart);
                });
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === 'insufficient_balance') {
                    return to_route('shop.cart.index')
                        ->with('error', trans('shop::messages.cart.errors.money'));
                }

                report($e);

                return to_route('shop.cart.index')
                    ->with('error', trans('shop::messages.cart.errors.execute'));
            } catch (Exception $e) {
                report($e);

                return to_route('shop.cart.index')
                    ->with('error', trans('shop::messages.cart.errors.execute'));
            }

            $cart->destroy();

            return to_route('shop.home')
                ->with('success', trans('shop::messages.cart.success'));
        }

        // Partial payment — persist the balance portion in the session so the
        // PaymentController can deduct it atomically once the gateway confirms.
        if ($balanceToUse > 0) {
            $request->session()->put('shop.pending_balance', $balanceToUse);
        }

        return to_route('shop.payments.payment');
    }
}
