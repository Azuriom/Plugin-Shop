<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display the shop settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $commands = setting('shop.commands');

        return view('shop::admin.settings', [
            'currencies' => Currencies::all(),
            'currentCurrency' => setting('currency', 'USD'),
            'goal' => (int) setting('shop.month_goal', 0),
            'commands' => $commands ? json_decode($commands) : [],
            'homeMessage' => setting('shop.home', ''),
        ]);
    }

    /**
     * Update the shop settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        $data = $this->validate($request, [
            'currency' => ['required', Rule::in(Currencies::codes())],
            'goal' => ['nullable', 'integer', 'min:0'],
            'webhook' => ['nullable', 'url'],
            'commands' => ['sometimes', 'nullable', 'array'],
        ]);

        $commands = $request->input('commands');

        Setting::updateSettings(Arr::only($data, 'currency'));

        Setting::updateSettings([
            'shop.use_site_money' => $request->has('use_site_money'),
            'shop.month_goal' => $request->input('goal'),
            'shop.webhook' => $request->input('webhook'),
            'shop.home' => $request->input('home-message'),
            'shop.commands' => is_array($commands) ? json_encode(array_filter($commands)) : null,
        ]);

        return redirect()->route('shop.admin.settings')
            ->with('success', trans('admin.settings.updated'));
    }
}
