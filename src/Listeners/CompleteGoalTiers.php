<?php

namespace Azuriom\Plugin\Shop\Listeners;

use Azuriom\Plugin\Shop\Events\PaymentPaid;
use Azuriom\Plugin\Shop\Models\GoalTier;

class CompleteGoalTiers
{
    /**
     * Handle the event.
     */
    public function handle(PaymentPaid $event): void
    {
        // Rewards can be delivered to many users, so do it after sending response
        dispatch(fn () => GoalTier::completeReached())->afterResponse();
    }
}
