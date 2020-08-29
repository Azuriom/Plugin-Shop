<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Package $package)
    {
        $package->load(['category.packages', 'discounts']);

        return view('shop::packages.show', ['package' => $package]);
    }

    /**
     * Buy the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function buy(Request $request, Package $package)
    {
        $this->validate($request, ['quantity' => 'nullable|integer']);

        $cart = Cart::fromSession($request->session());

        if ($package->getMaxQuantity() < 1) {
            return redirect()->back()->with('error', trans('shop::messages.packages.limit'));
        }

        if (! $package->hasBoughtRequirements()) {
            return redirect()->back()->with('error', trans('shop::messages.packages.requirements'));
        }

        $cart->add($package, $request->input('quantity', 1));

        return redirect()->route('shop.cart.index');
    }
}
