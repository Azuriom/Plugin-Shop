<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Discount;
use Azuriom\Plugin\Shop\Requests\DiscountRequest;
use Illuminate\Support\Arr;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('shop::admin.discounts.index', ['discounts' => Discount::all()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shop::admin.discounts.create', [
            'categories' => Category::with('packages')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\DiscountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DiscountRequest $request)
    {
        $discount = Discount::create(Arr::except($request->validated(), 'packages'));

        $discount->packages()->sync($request->input('packages'));

        return redirect()->route('shop.admin.discounts.index')->with('success', 'Discount created');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit(Discount $discount)
    {
        return view('shop::admin.discounts.edit', [
            'discount' => $discount->load('packages'),
            'categories' => Category::with('packages')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\DiscountRequest  $request
     * @param  \Azuriom\Plugin\Shop\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(DiscountRequest $request, Discount $discount)
    {
        $discount->update(Arr::except($request->validated(), 'packages'));

        $discount->packages()->sync($request->input('packages'));

        return redirect()->route('shop.admin.discounts.index')->with('success', 'Discount updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('shop.admin.discounts.index')->with('success', 'Discount deleted');
    }
}
