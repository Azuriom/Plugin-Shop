<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Server;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display the shop settings.
     */
    public function show()
    {
        $commands = setting('shop.commands');

        return view('shop::admin.settings', [
            'currencies' => Currencies::all(),
            'currentCurrency' => setting('currency', 'USD'),
            'goal' => (int) setting('shop.month_goal', 0),
            'topCustomer' => setting('shop.top_customer', false),
            'recentPayments' => (int) setting('shop.recent_payments', 0),
            'displayAmount' => setting('shop.display_amount', true),
            'commands' => $commands ? json_decode($commands) : [],
            'commandTriggers' => Package::COMMAND_TRIGGERS,
            'servers' => Server::executable()->get(),
            'enableHome' => setting('shop.home.enabled', true),
            'homeMessage' => setting('shop.home', ''),
            'termsRequired' => old('terms_required', setting('shop.required_terms') !== null),
        ]);
    }

    /**
     * Update the shop settings.
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
            'terms' => ['required_with:terms_required', 'nullable', 'string'],
        ]);

        $commands = $request->input('commands');

        Setting::updateSettings(Arr::only($data, 'currency'));

        Setting::updateSettings([
            'shop.use_site_money' => $request->has('use_site_money'),
            'shop.month_goal' => $request->input('goal'),
            'shop.recent_payments' => $request->input('recent_payments'),
            'shop.top_customer' => $request->filled('top_customer'),
            'shop.display_amount' => $request->filled('display_amount'),
            'shop.webhook' => $request->input('webhook'),
            'shop.home' => $request->input('home_message'),
            'shop.home.enabled' => $request->has('enable_home'),
            'shop.commands' => is_array($commands) ? json_encode($commands) : null,
            'shop.required_terms' => $request->filled('terms_required') ? $request->input('terms') : null,
        ]);

        ActionLog::log('shop.settings.updated');

        return to_route('shop.admin.settings')
            ->with('success', trans('admin.settings.updated'));
    }
}
