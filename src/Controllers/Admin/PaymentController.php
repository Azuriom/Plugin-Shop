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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $payments = Payment::notPending()
            ->with('user')
            ->latest()
            ->when($search, function (Builder $query, string $search) {
                $query->where('payment_id', 'LIKE', "%{$search}%");

                if (is_numeric($search)) {
                    $query->orWhere('id', $search)
                        ->orWhere('user_id', 'LIKE', "%{$search}%");
                }
            })->paginate();

        return view('shop::admin.payments.index', [
            'payments' => $payments,
            'search' => $search,
        ]);
    }
}
