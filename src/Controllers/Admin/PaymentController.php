<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $payments = Payment::scopes(['notPending', 'withRealMoney'])
            ->with('user')
            ->latest()
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('user', function (Builder $query) use ($search) {
                        $query->scopes(['search' => $search]);
                    })->orWhere('transaction_id', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $query->orWhere('id', $search);
                    }
                });
            })->paginate();

        return view('shop::admin.payments.index', [
            'payments' => $payments,
            'search' => $search,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        $payment->load(['user', 'items', 'coupons']);

        return view('shop::admin.payments.show', ['payment' => $payment]);
    }
}
