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
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('shop::admin.coupons.index', [
            'coupons' => Coupon::with('payments')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::with('packages')->whereHas('packages')->get();

        return view('shop::admin.coupons.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\CouponRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        $coupon->packages()->sync($request->input('packages'));

        return redirect()->route('shop.admin.coupons.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
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
     *
     * @param  \Azuriom\Plugin\Shop\Requests\CouponRequest  $request
     * @param  \Azuriom\Plugin\Shop\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        $coupon->packages()->sync($request->input('packages'));

        return redirect()->route('shop.admin.coupons.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('shop.admin.coupons.index')
            ->with('success', trans('messages.status.success'));
    }
}
