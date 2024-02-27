<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Role;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Discount;
use Azuriom\Plugin\Shop\Requests\DiscountRequest;
use Illuminate\Support\Arr;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('shop::admin.discounts.index', ['discounts' => Discount::all()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::with('packages')->whereHas('packages')->get();

        return view('shop::admin.discounts.create', [
            'categories' => $categories,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DiscountRequest $request)
    {
        $discount = Discount::create(Arr::except($request->validated(), 'packages'));

        $discount->packages()->sync($request->input('packages'));

        return to_route('shop.admin.discounts.index')->with('success', 'Discount created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount)
    {
        $categories = Category::with('packages')->whereHas('packages')->get();

        return view('shop::admin.discounts.edit', [
            'discount' => $discount->load('packages'),
            'categories' => $categories,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DiscountRequest $request, Discount $discount)
    {
        $discount->update(Arr::except($request->validated(), 'packages'));

        $discount->packages()->sync($request->input('packages'));

        return to_route('shop.admin.discounts.index')->with('success', 'Discount updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Discount $discount)
    {
        $discount->delete();

        return to_route('shop.admin.discounts.index')->with('success', 'Discount deleted');
    }
}
