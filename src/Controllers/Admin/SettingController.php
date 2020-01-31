<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display the settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('shop::admin.settings', [
            'currencies' => Currencies::all(),
            'currentCurrency' => setting('currency', 'USD'),
        ]);
    }

    public function save(Request $request)
    {
        Setting::updateSettings($this->validate($request, [
            'currency' => ['required', Rule::in(Currencies::codes())],
        ]));

        Setting::updateSettings('shop.use-site-money', $request->has('use-site-money'));

        return redirect()->route('shop.admin.settings')
            ->with('success', trans('admin.settings.status.updated'));
    }
}
