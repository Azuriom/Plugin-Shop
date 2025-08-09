<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Azuriom;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\HasUser;
use Azuriom\Models\Traits\Searchable;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Events\PaymentPaid;
use Azuriom\Plugin\Shop\Notifications\PaymentPaid as PaymentPaidNotification;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Azuriom\Support\Discord\DiscordWebhook;
use Azuriom\Support\Discord\Embed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $subscription_id
 * @property float $price
 * @property string $currency
 * @property string $status
 * @property string $gateway_type
 * @property string $transaction_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Models\User $user
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\PaymentItem[] $items
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Coupon[] $coupons
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Giftcard[] $giftcards
 * @property \Azuriom\Plugin\Shop\Models\Subscription|null $subscription
 *
 * @method static \Illuminate\Database\Eloquent\Builder completed()
 * @method static \Illuminate\Database\Eloquent\Builder pending()
 * @method static \Illuminate\Database\Eloquent\Builder notPending()
 * @method static \Illuminate\Database\Eloquent\Builder withSiteMoney()
 * @method static \Illuminate\Database\Eloquent\Builder withRealMoney()
 */
class Payment extends Model
{
    use HasTablePrefix;
    use HasUser;
    use Searchable;

    /**
     * The table prefix associated with the model.
     */
    protected string $prefix = 'shop_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'price', 'currency', 'status', 'gateway_type', 'transaction_id', 'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
    ];

    /**
     * The attributes that can be used for search.
     *
     * @var array<int, string>
     */
    protected array $searchable = [
        'status', 'gateway_type', 'transaction_id', 'subscription_id', 'user.*',
    ];

    /**
     * Get the category of this package.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items purchased in this payment.
     */
    public function items()
    {
        return $this->hasMany(PaymentItem::class)->inverse('payment');
    }

    /**
     * Get the coupons used in this payment.
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'shop_coupon_payment');
    }

    /**
     * Get the associated subscription if this payment is for a subscription.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the giftcards used in this payment.
     */
    public function giftcards()
    {
        return $this->belongsToMany(Giftcard::class, 'shop_giftcard_payment')
            ->withPivot('amount');
    }

    public function getTypeName(): string
    {
        if ($this->isWithSiteMoney()) {
            return site_name();
        }

        return Gateway::getNameByType($this->gateway_type);
    }

    public function deliver(bool $renewal = false): void
    {
        $this->update(['status' => 'completed']);

        foreach ($this->giftcards as $giftcard) {
            Cache::forget('shop.giftcards.pending.'.$giftcard->id);
        }

        foreach ($this->items as $item) {
            $item->deliver($renewal);
        }

        if (! $this->isWithSiteMoney()) {
            event(new PaymentPaid($this));
        }

        if (($webhookUrl = setting('shop.webhook')) !== null) {
            rescue(fn () => $this->createDiscordWebhook()->send($webhookUrl));
        }

        rescue(fn () => $this->user->notify(new PaymentPaidNotification($this)));
    }

    public function revoke(string $trigger)
    {
        foreach ($this->items as $item) {
            $item->revoke($trigger);
        }
    }

    public function processGiftcards(float $originalTotal, Collection $giftcards): float
    {
        return $giftcards
            ->filter(fn (Giftcard $card) => $card->isActive())
            ->reduce(function ($total, Giftcard $card) {
                if ($total <= 0) {
                    return 0;
                }

                $newTotal = max($total - $card->balance, 0);

                if ($newTotal > 0) {
                    $this->giftcards()->attach($card, [
                        'amount' => $card->balance,
                    ]);

                    $card->update(['balance' => 0]);
                } else {
                    $this->giftcards()->attach($card, [
                        'amount' => $total,
                    ]);

                    $card->decrement('balance', $total);
                }

                Cache::put('shop.giftcards.pending.'.$card->id, true, now()->addMinutes(15));

                return $newTotal;
            }, $originalTotal);
    }

    public function createDiscordWebhook(): DiscordWebhook
    {
        $title = trans('shop::messages.payment.webhook', ['id' => $this->id]);
        $embed = $this->getDiscordEmbed($title, '#004de6', true);

        return DiscordWebhook::create()->addEmbed($embed);
    }

    public function createRefundDiscordWebhook(bool $isChargeback = false): DiscordWebhook
    {
        $title = $isChargeback
            ? trans('shop::messages.payment.webhook_chargeback', ['id' => $this->id])
            : trans('shop::messages.payment.webhook_refund', ['id' => $this->id]);
        $color = $isChargeback ? '#dc3545' : '#ffc107';
        $embed = $this->getDiscordEmbed($title, $color, false);

        return DiscordWebhook::create()->addEmbed($embed);
    }

    private function getDiscordEmbed(string $title, string $color, bool $full): Embed
    {
        $lines = [
            trans('shop::mails.payment.total', [
                'total' => $this->formatPrice(),
            ]),
        ];

        if ($this->transaction_id !== null) {
            $lines[] = trans('shop::mails.payment.transaction', [
                'transaction' => $this->transaction_id,
                'gateway' => $this->getTypeName(),
            ]);
        }

        $embed = Embed::create()
            ->title($title)
            ->description(implode("\n", array_map(fn (string $s) => '- '.$s, $lines)))
            ->author($this->user->name, null, $this->user->getAvatar())
            ->url(route('shop.admin.payments.show', $this))
            ->color($color)
            ->footer('Azuriom v'.Azuriom::version())
            ->timestamp(now());

        foreach ($this->items as $item) {
            $name = $item->name;
            $quantity = $item->quantity > 1 ? " (x{$item->quantity})" : '';

            $lines = [
                trans('shop::messages.payment.price', ['price' => $item->formatPrice()]),
            ];

            if (! empty($item->variables)) {
                $lines[] = trans('shop::messages.payment.variables');

                foreach ($item->variables as $key => $value) {
                    $lines[] = "- **`{{$key}}`** {$value}";
                }
            }

            if ($full) {
                $embed->addField($name.$quantity, implode("\n", $lines), true);
            }
        }

        return $embed;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending', 'expired' => 'warning',
            'chargeback', 'error' => 'danger',
            'completed' => 'success',
            default => 'secondary',
        };
    }

    public function formatPrice(): string
    {
        return $this->isWithSiteMoney()
            ? shop_format_amount($this->price, true)
            : Currencies::formatAmount($this->price, $this->currency);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isWithSiteMoney(): bool
    {
        return $this->gateway_type === 'azuriom';
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeNotPending(Builder $query): void
    {
        $query->where('status', '!=', 'pending');
    }

    public function scopeWithSiteMoney(Builder $query): void
    {
        $query->where('gateway_type', '=', 'azuriom');
    }

    public function scopeWithRealMoney(Builder $query): void
    {
        $query->where('gateway_type', '!=', 'azuriom');
    }

    public static function purgePendingPayments(): void
    {
        self::pending()->where('created_at', '<', now()->subWeeks(2))->delete();
    }
}
