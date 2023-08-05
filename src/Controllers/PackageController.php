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
     */
    public function show(Package $package)
    {
        $package->load(['category.packages', 'discounts']);

        return view('shop::packages.show', ['package' => $package]);
    }

    /**
     * Buy the specified resource.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function buy(Request $request, Package $package)
    {
        $this->validate($request, [
            'quantity' => 'nullable|integer',
            'price' => 'sometimes|nullable|numeric|min:'.$package->price,
        ]);

        $user = $request->user();
        $cart = Cart::fromSession($request->session());

        if ($package->getMaxQuantity() < 1) {
            return redirect()->back()->with('error', trans('shop::messages.packages.limit'));
        }

        if (! $package->hasBoughtRequirements() || ! $package->hasRequiredRole($user->role)) {
            return redirect()->back()->with('error', trans('shop::messages.packages.requirements'));
        }

        $price = $package->custom_price ? $request->input('price') : null;

        $cart->add($package, $request->input('quantity') ?? 1, $price);

        return to_route('shop.cart.index');
    }
}
