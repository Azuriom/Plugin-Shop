<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Azuriom\Plugin\Shop\Requests\GatewayRequest;

class GatewayController extends Controller
{
    /**
     * The payment manager instance.
     *
     * @var PaymentManager
     */
    protected $paymentManager;

    /**
     * Create a new controller instance.
     *
     * @param  \Azuriom\Plugin\Shop\Payment\PaymentManager  $paymentManager
     */
    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gateways = Gateway::all()->filter(function ($gateway) {
            return payment_manager()->hasPaymentMethod($gateway->type);
        });

        $gatewayTypes = $gateways->pluck('type');

        $paymentMethods = $this->paymentManager->getPaymentMethods()
            ->keys()
            ->filter(function ($type) use ($gatewayTypes) {
                return ! $gatewayTypes->contains($type);
            });

        return view('shop::admin.gateways.index', [
            'gateways' => $gateways,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function create(string $type)
    {
        return view('shop::admin.gateways.create', [
            'type' => $this->paymentManager->getPaymentMethodOrFail($type),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\GatewayRequest  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(GatewayRequest $request)
    {
        $type = $request->input('type');

        $method = $this->paymentManager->getPaymentMethodOrFail($type);

        $data = $this->validate($request, $method->rules());

        Gateway::create([
            'data' => $data,
            'type' => $type,
        ] + $request->validated());

        return redirect()->route('shop.admin.gateways.index')
            ->with('success', trans('shop::admin.gateways.status.created'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Gateway  $gateway
     * @return \Illuminate\Http\Response
     */
    public function edit(Gateway $gateway)
    {
        return view('shop::admin.gateways.edit', [
            'type' => $gateway->paymentMethod(),
            'gateway' => $gateway,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\GatewayRequest  $request
     * @param  \Azuriom\Plugin\Shop\Models\Gateway  $gateway
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(GatewayRequest $request, Gateway $gateway)
    {
        $data = $this->validate($request, $gateway->paymentMethod()->rules());

        $gateway->update(['data' => $data] + $request->validated());

        return redirect()->route('shop.admin.gateways.index')
            ->with('success', trans('shop::admin.gateways.status.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Gateway  $gateway
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Gateway $gateway)
    {
        $gateway->delete();

        return redirect()->route('shop.admin.gateways.index')
            ->with('success', trans('shop::admin.gateways.status.deleted'));
    }
}
