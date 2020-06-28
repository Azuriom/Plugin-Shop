<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Coupon;
use Azuriom\Plugin\Shop\Models\Package;
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
        return view('shop::admin.coupons.index', ['coupons' => Coupon::all()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $packages = Package::with('category')
            ->get()
            ->groupBy('category.name');

        return view('shop::admin.coupons.create', ['packages' => $packages]);
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
            ->with('success', trans('shop::admin.coupons.status.deleted'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        $packages = Package::with('category')
            ->get()
            ->groupBy('category.name');

        return view('shop::admin.coupons.edit', [
            'coupon' => $coupon,
            'packages' => $packages,
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
            ->with('success', trans('shop::admin.coupons.status.updated'));
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
            ->with('success', trans('shop::admin.coupons.status.deleted'));
    }
}
