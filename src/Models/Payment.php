<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Azuriom;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\HasUser;
use Azuriom\Models\Traits\Searchable;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Events\PaymentPaid;
use Azuriom\Plugin\Shop\Notifications\PaymentPaid as PaymentPaidNotification;
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
        return $this->hasMany(PaymentItem::class);
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

    public function dispatchCommands(string $status): void
    {
        foreach ($this->items as $item) {
            $item->dispatchCommands($status);
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
        $transactionId = $this->isWithSiteMoney() ? '#'.$this->id : $this->transaction_id;

        $embed = Embed::create()
            ->title(trans('shop::messages.payment.webhook'))
            ->description(trans('shop::messages.payment.webhook_info', [
                'total' => $this->formatPrice(),
                'gateway' => $this->getTypeName(),
                'id' => $transactionId ?? trans('messages.none'),
            ]))
            ->author($this->user->name, null, $this->user->getAvatar())
            ->url(route('shop.admin.payments.show', $this))
            ->color('#004de6')
            ->footer('Azuriom v'.Azuriom::version())
            ->timestamp(now());

        foreach ($this->items as $item) {
            $name = $item->name;

            if ($item->quantity > 1) {
                $name .= ' (x'.$item->quantity.')';
            }

            $embed->addField($name, $item->formatPrice());
        }

        return DiscordWebhook::create()->addEmbed($embed);
    }

    public function createRefundDiscordWebhook(bool $isChargeback = false): DiscordWebhook
    {
        $transactionId = $this->isWithSiteMoney() ? '#'.$this->id : $this->transaction_id;
        $title = $isChargeback
            ? trans('shop::messages.payment.webhook_chargeback')
            : trans('shop::messages.payment.webhook_refund');

        $embed = Embed::create()
            ->title($title)
            ->description(trans('shop::messages.payment.webhook_info', [
                'total' => $this->formatPrice(),
                'gateway' => $this->getTypeName(),
                'id' => $transactionId ?? trans('messages.none'),
            ]))
            ->author($this->user->name, null, $this->user->getAvatar())
            ->url(route('shop.admin.payments.show', $this))
            ->color($isChargeback ? '#dc3545' : '#ffc107')
            ->footer('Azuriom v'.Azuriom::version())
            ->timestamp(now());

        return DiscordWebhook::create()->addEmbed($embed);
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
        $currency = $this->isWithSiteMoney()
            ? money_name($this->price)
            : currency_display($this->currency);

        return $this->price.' '.$currency;
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
