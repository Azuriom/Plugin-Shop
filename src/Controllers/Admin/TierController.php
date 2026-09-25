<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Server;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Shop\Models\Tier;
use Azuriom\Plugin\Shop\Requests\TierRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiers = Tier::orderBy('min_spend')->orderBy('position')->get();
        $unlockedCount = DB::table('shop_tier_user')->count();
        $lastReset = setting('shop.tiers_reset_at');

        return view('shop::admin.tiers.index', [
            'tiers' => $tiers,
            'unlockedCount' => $unlockedCount,
            'lastReset' => $lastReset ? Carbon::parse($lastReset) : null,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $servers = Server::executable()->pluck('name', 'id');

        return view('shop::admin.tiers.create', [
            'servers' => $servers,
            'commands' => old('commands', [
                ['server' => $servers->keys()->first() ?? 0, 'require_online' => 0, 'commands' => ['']],
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TierRequest $request)
    {
        Tier::create($request->validated());

        return to_route('shop.admin.tiers.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tier $tier)
    {
        $servers = Server::executable()->pluck('name', 'id');

        return view('shop::admin.tiers.edit', [
            'tier' => $tier,
            'servers' => $servers,
            'commands' => old('commands', $tier->commands ?? [
                ['server' => $servers->keys()->first() ?? 0, 'require_online' => 0, 'commands' => ['']],
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TierRequest $request, Tier $tier)
    {
        $tier->update($request->validated());

        return to_route('shop.admin.tiers.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tier $tier)
    {
        $tier->delete();

        return to_route('shop.admin.tiers.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Update the order of the resources.
     */
    public function updateOrder(Request $request)
    {
        $this->validate($request, [
            'tiers' => ['required', 'array'],
        ]);

        foreach ($request->input('tiers') as $position => $id) {
            Tier::whereKey($id)->update(['position' => $position]);
        }

        return response()->json([
            'message' => trans('messages.status.success'),
        ]);
    }

    /**
     * Reset all users' progression on all tiers.
     */
    public function resetAll(Request $request)
    {
        Setting::updateSettings([
            'shop.tiers_reset_at' => now()->toDateTimeString(),
        ]);

        DB::table('shop_tier_user')->delete();

        ActionLog::log('shop-tiers.reset');

        return to_route('shop.admin.tiers.index')
            ->with('success', trans('shop::admin.tiers.reset_success'));
    }
}
