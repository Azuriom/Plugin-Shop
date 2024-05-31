<?php

namespace Azuriom\Plugin\Shop\Observers;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Models\GatewayMetadata;
use Azuriom\Plugin\Shop\Models\Subscription;

class UserObserver
{
    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $subscriptions = Subscription::where('user_id', $user->id)->get();

        foreach ($subscriptions as $subscription) {
            $subscription->cancel();
        }

        GatewayMetadata::whereMorphedTo('model', $user)->delete();
    }
}
