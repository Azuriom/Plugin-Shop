<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $purchases = Payment::withSiteMoney()
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

        return view('shop::admin.purchases.index', [
            'purchases' => $purchases,
            'search' => $search,
        ]);
    }
}
