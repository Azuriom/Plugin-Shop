<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Offer;
use Azuriom\Plugin\Shop\Requests\OfferRequest;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('shop::admin.offers.index', ['offers' => Offer::all()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shop::admin.offers.create', ['gateways' => $this->getGateways()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\OfferRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OfferRequest $request)
    {
        $offer = Offer::create($request->validated());

        $gateways = array_keys($request->input('gateways', []));

        $offer->gateways()->sync($gateways);

        return redirect()->route('shop.admin.offers.index')
            ->with('success', trans('shop::admin.offers.status.created'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Offer  $offer
     * @return \Illuminate\Http\Response
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
     *
     * @param  \Azuriom\Plugin\Shop\Requests\OfferRequest  $request
     * @param  \Azuriom\Plugin\Shop\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function update(OfferRequest $request, Offer $offer)
    {
        $offer->update($request->validated());

        $gateways = array_keys($request->input('gateways', []));

        $offer->gateways()->sync($gateways);

        return redirect()->route('shop.admin.offers.index')
            ->with('success', trans('shop::admin.offers.status.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()->route('shop.admin.offers.index')
            ->with('success', trans('shop::admin.offers.status.deleted'));
    }

    private function getGateways()
    {
        return Gateway::all()->filter(function (Gateway $gateway) {
            return payment_manager()->hasPaymentMethod($gateway->type) && ! $gateway->paymentMethod()->hasFixedAmount();
        });
    }
}
