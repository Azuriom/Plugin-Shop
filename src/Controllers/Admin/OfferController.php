<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Offer;
use Azuriom\Plugin\Shop\Requests\OfferRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('shop::admin.offers.index', ['offers' => Offer::all()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shop::admin.offers.create', ['gateways' => $this->getGateways()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OfferRequest $request)
    {
        $offer = Offer::create(Arr::except($request->validated(), ['image', 'gateways']));

        $offer->gateways()->sync($request->input('gateways', []));

        if ($request->hasFile('image')) {
            $offer->storeImage($request->file('image'), true);
        }

        return to_route('shop.admin.offers.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Offer $offer)
    {
        return view('shop::admin.offers.edit', [
            'offer' => $offer->load('gateways'),
            'gateways' => $this->getGateways(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OfferRequest $request, Offer $offer)
    {
        $offer->update(Arr::except($request->validated(), ['image', 'gateways']));

        if ($request->hasFile('image')) {
            $offer->storeImage($request->file('image'), true);
        }

        $offer->gateways()->sync($request->input('gateways', []));

        return to_route('shop.admin.offers.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Offer $offer)
    {
        $offer->delete();

        return to_route('shop.admin.offers.index')
            ->with('success', trans('messages.status.success'));
    }

    private function getGateways(): Collection
    {
        return Gateway::all()
            ->filter(fn (Gateway $gateway) => $gateway->isSupported())
            ->reject(fn (Gateway $gateway) => $gateway->paymentMethod()->hasFixedAmount());
    }
}
