<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Server;
use Azuriom\Plugin\Shop\Models\GoalTier;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Azuriom\Plugin\Shop\Requests\GoalTierRequest;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('shop::admin.goals.index', [
            'tiers' => GoalTier::with('currentCompletion')->orderBy('amount')->get(),
            'total' => $this->formatCurrentTotal(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shop::admin.goals.create', [
            'servers' => Server::executable()->pluck('name', 'id'),
            'total' => $this->formatCurrentTotal(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GoalTierRequest $request)
    {
        GoalTier::create($request->validated());

        return to_route('shop.admin.goals.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoalTier $goal)
    {
        return view('shop::admin.goals.edit', [
            'tier' => $goal,
            'completions' => $goal->completions()->latest()->get(),
            'servers' => Server::executable()->pluck('name', 'id'),
            'total' => $this->formatCurrentTotal(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GoalTierRequest $request, GoalTier $goal)
    {
        $goal->update($request->validated());

        return to_route('shop.admin.goals.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(GoalTier $goal)
    {
        $goal->delete();

        return to_route('shop.admin.goals.index')
            ->with('success', trans('messages.status.success'));
    }

    protected function formatCurrentTotal(): string
    {
        return Currencies::formatAmount(Payment::currentMonthTotal(), currency());
    }
}
