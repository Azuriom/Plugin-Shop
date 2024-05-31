<?php

namespace Azuriom\Plugin\Shop\Commands;

use Azuriom\Plugin\Shop\Models\PaymentItem;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\Builder;

class PaymentExpireCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired payments.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Removing expired payments...');

        $expired = PaymentItem::whereHas('payment', function (Builder $query) {
            $query->scopes('completed');
        })
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $payment) {
            $payment->expire();
        }

        $this->info('Removed '.$expired->count().' expired payments.');
    }
}
