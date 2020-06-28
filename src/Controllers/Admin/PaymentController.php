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

        $payments = Payment::notPending()
            ->with('user')
            ->latest()
            ->when($search, function (Builder $query, string $search) {
                $users = User::where('name', 'LIKE', "%{$search}")
                    ->orWhere('game_id', 'LIKE', "%{$search}")
                    ->get()
                    ->modelKeys();

                $query->where('payment_id', 'LIKE', "%{$search}%");

                if ($users) {
                    $query->orWhereIn('user_id', $users);
                }

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
