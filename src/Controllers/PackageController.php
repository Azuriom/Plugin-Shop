<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Cart\CartItem;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

    public function showVariables(Request $request, Package $package)
    {
        abort_if($package->variables->isEmpty(), 404);

        return view('shop::packages.variables', [
            'package' => $package,
            'price' => old('price', $request->input('price')),
            'quantity' => old('quantity', $request->input('quantity')),
        ]);
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

        if ($package->isSubscription()) {
            // TODO Remove legacy themes support...
            return app(SubscriptionController::class)->selectGateway($request, $package);
        }

        $user = $request->user();
        $cart = Cart::fromSession($request->session());

        if ($package->getMaxQuantity() < 1 || $package->category->hasReachLimit($user)) {
            return redirect()->back()->with('error', trans('shop::messages.packages.limit'));
        }

        if (! $package->hasBoughtRequirements() || ! $package->hasRequiredRole($user->role)) {
            return redirect()->back()->with('error', trans('shop::messages.packages.requirements'));
        }

        if ($this->categoryCumulateError($cart, $package)) {
            return redirect()->back()->with('error', trans('shop::messages.packages.cumulate'));
        }

        $price = $package->custom_price ? $request->input('price') : null;

        if ($package->variables->isEmpty()) {
            $cart->add($package, $request->input('quantity') ?? 1, $price);

            return to_route('shop.cart.index');
        }

        if ($request->routeIs('shop.packages.variables')) {
            return $this->buyWithVariables($request, $cart, $package, $price);
        }

        return redirect()->route('shop.packages.variables', [
            'package' => $package,
            'quantity' => $request->input('quantity'),
            'price' => $price,
        ]);
    }

    private function buyWithVariables(Request $request, Cart $cart, Package $package, ?float $price)
    {
        $rules = $package->variables->mapWithKeys(fn (Variable $variable) => [
            $variable->name => $variable->getValidationRule(),
        ]);

        $this->validate($request, array_merge([
            'quantity' => 'nullable|integer',
            'price' => 'sometimes|nullable|numeric|min:'.$package->price,
        ], $rules->all()));

        $checkboxes = $package->variables
            ->where('type', 'checkbox')
            ->mapWithKeys(fn (Variable $variable) => [
                $variable->name => $request->has($variable->name) ? 'true' : 'false',
            ]);

        $request->merge($checkboxes->all());

        $item = $cart->add($package, $request->input('quantity') ?? 1, $price);
        $item->variables = $request->only($package->variables->pluck('name')->toArray());
        $cart->save();

        return to_route('shop.cart.index');
    }

    private function categoryCumulateError(Cart $cart, Package $package)
    {
        if (! $package->category->cumulate_purchases && ! $package->category->single_purchase) {
            return false;
        }

        return $cart->content()->contains(function (CartItem $item) use ($package) {
            $other = $item->buyable();

            if (! $other instanceof Package || $other->getId() === $package->id) {
                return false;
            }

            return $other->category_id === $package->category_id;
        });
    }

    public function downloadFile(Package $package, string $file)
    {
        $fileName = Arr::get($package->files ?? [], $file);

        abort_if($fileName === null, 404);
        abort_if($package->countUserPurchases() === 0, 403);

        return $package->downloadFile($file, $fileName);
    }
}
