<?php

namespace Azuriom\Plugin\Shop\View\Composers;

use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Models\Tier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ShopViewComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Route::is('admin.*')) {
            return;
        }

        $user = shop_user();

        $tierProgress = null;
        $tiers = Tier::enabled()->orderBy('min_spend')->orderBy('position')->get();

        if ($tiers->isNotEmpty()) {
            if ($user !== null) {
                rescue(fn () => Tier::checkUserProgression($user));

                $totalSpent = Tier::getUserTotalSpent($user);
                $unlockedRows = DB::table('shop_tier_user')
                    ->where('user_id', $user->id)
                    ->get()
                    ->keyBy('tier_id');

                $unlockedIds = $unlockedRows->keys()->all();
                $unlockedDetails = $unlockedRows->map(fn ($r) => json_decode($r->reward_details ?? '[]', true))->all();
            } else {
                $totalSpent = 0.0;
                $unlockedIds = [];
                $unlockedDetails = [];
            }

            $totalCount = $tiers->count();
            $unlockedCount = count($unlockedIds);
            $nextTier = $tiers->first(fn ($t) => ! in_array($t->id, $unlockedIds, true));
            $remainingToNext = $nextTier !== null ? max(0, $nextTier->min_spend - $totalSpent) : 0.0;

            // Calculate progress percentage
            $percentage = 0.0;
            if ($totalCount > 0) {
                if ($unlockedCount >= $totalCount) {
                    $percentage = 100.0;
                } else {
                    $stepPercent = 100.0 / $totalCount;
                    $basePercent = $unlockedCount * $stepPercent;

                    $prevSpend = $unlockedCount > 0 ? $tiers[$unlockedCount - 1]->min_spend : 0.0;
                    $nextSpend = $nextTier !== null ? $nextTier->min_spend : $prevSpend;
                    $diff = $nextSpend - $prevSpend;

                    $extraPercent = $diff > 0
                        ? (min(max(0.0, $totalSpent - $prevSpend), $diff) / $diff) * $stepPercent
                        : 0.0;

                    $percentage = min(100.0, round($basePercent + $extraPercent, 1));
                }
            }

            $tierProgress = [
                'tiers' => $tiers,
                'totalSpent' => $totalSpent,
                'unlockedIds' => $unlockedIds,
                'unlockedDetails' => $unlockedDetails,
                'unlockedCount' => $unlockedCount,
                'totalCount' => $totalCount,
                'nextTier' => $nextTier,
                'remainingToNext' => $remainingToNext,
                'percentage' => $percentage,
            ];
        }

        $view->with([
            'shopUser' => $user,
            'guestShopLogin' => ! use_site_money() && setting('shop.guest_purchases', false),
            'userHasPayments' => $user !== null && Payment::whereBelongsTo($user)->exists(),
            'tierProgress' => $tierProgress,
        ]);
    }
}
