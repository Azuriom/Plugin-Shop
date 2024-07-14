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
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $payments = Payment::scopes(['notPending', 'withRealMoney'])
            ->with('user')
            ->when($search, fn (Builder $query) => $query->search($search))
            ->latest()
            ->paginate();

        return view('shop::admin.payments.index', [
            'payments' => $payments,
            'search' => $search,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['user', 'items', 'coupons']);

        foreach ($payment->items as $item) {
            $item->setRelation('payment', $payment);
        }

        return view('shop::admin.payments.show', ['payment' => $payment]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::with('packages')->whereHas('packages')->get();

        $quantifiablePackages = $categories->pluck('packages')
            ->flatten()
            ->where('has_quantity', true)
            ->pluck('id');

        return view('shop::admin.payments.create', [
            'categories' => $categories,
            'quantifiablePackages' => $quantifiablePackages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        $packageIds = Arr::pluck($request->input('packages', []), 'quantity', 'id');
        $attributes = array_merge($request->validated(), [
            'currency' => currency(),
            'status' => 'completed',
            'gateway_type' => 'manual',
        ]);

        $payment = Payment::create(Arr::except($attributes, 'packages'));
        $packages = Package::findMany(array_keys($packageIds));

        foreach ($packages as $package) {
            $payment->items()
                ->make([
                    'name' => $package->name,
                    'price' => 0,
                    'quantity' => Arr::get($packageIds, $package->id) ?? 1,
                ])
                ->buyable()
                ->associate($package)
                ->save();
        }

        $payment->deliver();

        return to_route('shop.admin.payments.show', $payment);
    }
}
