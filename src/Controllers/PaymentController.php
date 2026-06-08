<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        if ($request->user() !== null && $request->user()->email === null && $request->filled('email')) {
            $this->validate($request, ['email' => 'required|email|max:50|unique:users']);

            $request->user()->update(['email' => $request->input('email')]);
        }

        $cart = Cart::fromSession($request->session());

        $gatewayTotal = $this->resolveGatewayTotal($request, $cart);

        // If the cart is not empty but nothing is owed to the gateway,
        // complete the payment immediately (fully covered by balance or giftcards).
        if (! $cart->isEmpty() && $gatewayTotal < 0.1) {
            $this->deductPendingBalance($request, $cart);

            PaymentManager::createPayment($cart, 0, currency(), 'free')->deliver();

            $cart->destroy();

            return to_route('shop.home')->with('success', trans('shop::messages.cart.success'));
        }

        $gateways = Gateway::enabled()
            ->get()
            ->filter(fn (Gateway $gateway) => $gateway->isSupported())
            ->reject(fn (Gateway $gateway) => $gateway->paymentMethod()->hasFixedAmount());

        // If there is only one payment gateway, redirect to it directly.
        if ($gateways->count() === 1) {
            return $gateways->first()->paymentMethod()->startPayment($cart, $gatewayTotal, currency());
        }

        return view('shop::payments.pay', [
            'gateways' => $gateways,
            'gatewayTotal' => $gatewayTotal,
            'balanceUsed' => $request->session()->get('shop.pending_balance', $cart->getBalanceAmount()),
        ]);
    }

    /**
     * Start a new payment via a specific gateway.
     */
    public function pay(Request $request, Gateway $gateway)
    {
        abort_if(! $gateway->is_enabled, 403);

        $cart = Cart::fromSession($request->session());

        if ($cart->isEmpty()) {
            return to_route('shop.cart.index');
        }

        return $gateway->paymentMethod()->startPayment(
            $cart,
            $this->resolveGatewayTotal($request, $cart),
            currency()
        );
    }

    public function success(Request $request, Gateway $gateway)
    {
        // Deduct the balance portion atomically before acknowledging the gateway.
        // If this fails the gateway response is still returned so the payment
        // record is not lost, but the error is reported for manual review.
        try {
            $this->deductPendingBalance($request, Cart::fromSession($request->session()));
        } catch (\Throwable $e) {
            report($e);
        }

        $response = $gateway->paymentMethod()->success($request);

        Cart::fromSession($request->session())->destroy();

        return $response;
    }

    public function failure(Request $request, Gateway $gateway)
    {
        // Do not deduct balance on failure — the pending_balance session key is
        // intentionally preserved so the user can retry with the same split.
        return $gateway->paymentMethod()->failure($request);
    }

    /**
     * Determine the amount that must be charged via the external gateway.
     *
     * Prefers an explicit pending_balance stored in the session (set by
     * CartController when the user chose a partial balance split) over the
     * cart's own balanceAmount, so that the value cannot be tampered with
     * between the confirmation modal and the actual gateway redirect.
     */
    private function resolveGatewayTotal(Request $request, Cart $cart): float
    {
        $pendingBalance = (float) $request->session()->get('shop.pending_balance', 0.0);

        if ($pendingBalance > 0.0) {
            return max($cart->payableTotal() - $pendingBalance, 0.0);
        }

        return $cart->gatewayTotal();
    }

    /**
     * Deduct the pending balance from the user's account inside a DB transaction
     * with a pessimistic lock to prevent double-spend across concurrent sessions.
     */
    private function deductPendingBalance(Request $request, Cart $cart): void
    {
        $pending = (float) $request->session()->get('shop.pending_balance', 0.0);

        if ($pending <= 0.0 || $request->user() === null) {
            return;
        }

        DB::transaction(function () use ($request, $cart, $pending) {
            $user = $request->user()
                ->newQuery()
                ->lockForUpdate()
                ->findOrFail($request->user()->id);

            $toDeduct = min($pending, $cart->payableTotal());

            if (! $user->hasMoney($toDeduct)) {
                throw new \RuntimeException(
                    "Insufficient balance when deducting pending_balance for user {$user->id}."
                );
            }

            $user->removeMoney($toDeduct);
        });

        $request->session()->forget('shop.pending_balance');
    }
}
