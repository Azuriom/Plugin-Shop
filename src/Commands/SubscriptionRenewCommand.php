<?php

namespace Azuriom\Plugin\Shop\Commands;

use Azuriom\Plugin\Shop\Models\Subscription;
use Illuminate\Console\Command;

class SubscriptionRenewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew subscriptions and handle expired ones.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Renewing subscriptions...');

        $subscriptions = Subscription::waitingForRenewal()->get();
        $renewed = 0;

        foreach ($subscriptions as $subscription) {
            if ($subscription->user->hasMoney($subscription->price)) {
                $subscription->user->removeMoney($subscription->price);

                $payment = $subscription->addRenewalPayment();
                $payment->deliver(true);

                $renewed++;
            }
        }

        $this->info('Renewed '.$renewed.' subscriptions.');

        $this->info('Cancelling expired subscriptions...');

        $subscriptions = Subscription::pendingExpiration()->get();

        foreach ($subscriptions as $subscription) {
            $subscription->expire();
        }

        $this->info('Canceled '.$subscriptions->count().' expired subscriptions.');
    }
}
