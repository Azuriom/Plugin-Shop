<?php

namespace Azuriom\Plugin\Shop\Controllers\Api;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Models\GoalTier;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Support\Facades\DB;

class ShopController
{
    public function index()
    {
        return response()->json([
            'currency' => currency(),
            'goal' => $this->monthGoal(),
            'top' => $this->topCustomers(),
            'recent' => $this->recentPayments(),
        ]);
    }

    protected function monthGoal(): array
    {
        $tiers = GoalTier::with('currentCompletion')->orderBy('amount')->get();

        return [
            'progress' => round(Payment::currentMonthTotal(), 2),
            'total' => (float) ($tiers->last()?->amount ?? 0),
            'tiers' => $tiers->map(fn (GoalTier $tier) => [
                'id' => $tier->id,
                'name' => $tier->name,
                'icon' => $tier->icon,
                'amount' => $tier->amount,
                'reached' => $tier->isReached(),
            ])->all(),
        ];
    }

    protected function recentPayments(int $limit = 10): array
    {
        return Payment::scopes(['completed', 'withRealMoney'])
            ->with('user')
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn (Payment $payment) => [
                'user' => $this->mapUser($payment->user),
                'amount' => $payment->price,
                'currency' => $payment->currency,
                'timestamp' => $payment->created_at->toIso8601String(),
            ])
            ->all();
    }

    protected function topCustomers(int $limit = 10): array
    {
        $column = Payment::query()->getGrammar()->wrap('price');

        return Payment::scopes(['completed', 'withRealMoney', 'currentMonth'])
            ->select(['user_id', DB::raw("sum({$column}) as price")])
            ->with('user')
            ->where('price', '>', 0)
            ->groupBy('user_id')
            ->orderByDesc('price')
            ->take($limit)
            ->get()
            ->each(fn (Payment $payment) => $payment->forceFill([
                'currency' => currency(),
                'gateway_type' => 'none',
            ]))
            ->map(fn (Payment $payment) => [
                'user' => $this->mapUser($payment->user),
                'amount' => $payment->price,
                'currency' => $payment->currency,
            ])
            ->all();
    }

    protected function mapUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'game_id' => $user->game_id,
        ];
    }
}
