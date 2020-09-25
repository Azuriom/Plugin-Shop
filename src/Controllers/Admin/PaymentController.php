<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
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
                $users = User::search($search)->get();

                $query->where(function (Builder $query) use ($users, $search) {
                    $query->where('transaction_id', 'LIKE', "%{$search}%");

                    if (! $users->isEmpty()) {
                        $query->orWhereIn('user_id', $users->modelKeys());
                    }

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
