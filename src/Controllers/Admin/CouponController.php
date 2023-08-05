<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Coupon;
use Azuriom\Plugin\Shop\Requests\CouponRequest;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('shop::admin.coupons.index', [
            'coupons' => Coupon::with('payments')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::with('packages')->whereHas('packages')->get();

        return view('shop::admin.coupons.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        $coupon->packages()->sync($request->input('packages'));

        return to_route('shop.admin.coupons.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $coupon)
    {
        $categories = Category::with('packages')
            ->whereHas('packages')
            ->get();

        $payments = $coupon->payments()->with('user')->paginate();

        return view('shop::admin.coupons.edit', [
            'coupon' => $coupon,
            'categories' => $categories,
            'payments' => $payments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        $coupon->packages()->sync($request->input('packages'));

        return to_route('shop.admin.coupons.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return to_route('shop.admin.coupons.index')
            ->with('success', trans('messages.status.success'));
    }
}
