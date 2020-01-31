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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Package $package)
    {
        abort_if(! $request->pjax(), 403);

        return view('shop::packages.show', ['package' => $package]);
    }

    /**
     * Buy the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function buy(Request $request, Package $package)
    {
        $this->validate($request, ['quantity' => 'nullable|integer']);

        $cart = new Cart($request->session());

        if ($package->has_quantity) {
            $cart->add($package, $request->input('quantity', 1));
        } else {
            $cart->set($package);
        }

        return redirect()->route('shop.cart.index');
    }
}
