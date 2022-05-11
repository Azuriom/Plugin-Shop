<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Requests\PaymentRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('user', function (Builder $query) use ($search) {
                        $query->scopes(['search' => $search]);
                    })->orWhere('transaction_id', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $query->orWhere('id', $search);
                    }
                });
            })
            ->latest()
            ->paginate();

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::with('packages')->whereHas('packages')->get();

        return view('shop::admin.payments.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\PaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PaymentRequest $request)
    {
        $attributes = array_merge($request->validated(), [
            'price' => 0,
            'currency' => currency(),
            'status' => 'completed',
            'gateway_type' => 'manual',
        ]);

        $payment = Payment::create(Arr::except($attributes, 'packages'));
        $packages = Package::findMany($request->input('packages'));

        foreach ($packages as $package) {
            $payment->items()
                ->make([
                    'name' => $package->name,
                    'price' => 0,
                    'quantity' => 1,
                ])
                ->buyable()->associate($package)
                ->save();
        }

        $payment->deliver();

        return redirect()->route('shop.admin.payments.show', $payment);
    }
}
