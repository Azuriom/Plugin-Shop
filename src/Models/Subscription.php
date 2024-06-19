<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Searchable;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $package_id
 * @property string $subscription_id
 * @property string $gateway_type
 * @property string $status
 * @property float $price
 * @property string $currency
 * @property \Carbon\Carbon|null $ends_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Models\User $user
 * @property \Azuriom\Plugin\Shop\Models\Package|null $package
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Payment[] $payments
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder pendingExpiration()
 * @method static \Illuminate\Database\Eloquent\Builder notPending()
 * @method static \Illuminate\Database\Eloquent\Builder waitingForRenewal()
 */
class Subscription extends Model
{
    use HasTablePrefix;
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
        'user_id', 'subscription_id', 'gateway_type', 'status', 'price', 'currency', 'ends_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ends_at' => 'datetime',
    ];

    /**
     * The attributes that can be used for search.
     *
     * @var array<int, string>
     */
    protected array $searchable = [
        'status', 'gateway_type', 'subscription_id', 'user.*',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active' && $this->status !== 'canceled') {
            return false;
        }

        return ! $this->isEnded();
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    public function isEnded(): bool
    {
        return $this->ends_at === null || $this->ends_at->isPast();
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'canceled', 'expired' => 'warning',
            'active' => 'success',
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

    public function getTypeName(): string
    {
        return Gateway::getNameByType($this->gateway_type);
    }

    public function isWithSiteMoney(): bool
    {
        return $this->gateway_type === 'azuriom';
    }

    public function cancel(): void
    {
        if (! $this->isWithSiteMoney()) {
            $gateway = Gateway::firstWhere('type', $this->gateway_type);

            $gateway->paymentMethod()->cancelSubscription($this);
        }

        $this->update(['status' => 'canceled']);
    }

    public function addRenewalPayment(?string $transactionId = null): Payment
    {
        $payment = $this->payments()->create([
            'user_id' => $this->user_id,
            'price' => $this->price,
            'currency' => $this->currency,
            'gateway_type' => $this->gateway_type,
            'status' => 'completed',
            'transaction_id' => $transactionId,
        ]);

        $payment->items()
            ->make([
                'name' => $this->package->name,
                'price' => $this->price,
                'quantity' => 1,
            ])
            ->buyable()
            ->associate($this->package)
            ->save();

        $expiration = $this->ends_at ?? now();

        $this->update([
            'status' => 'active',
            'ends_at' => $expiration->add($this->package->billing_period)->endOfDay(),
        ]);

        return $payment;
    }

    public function expire(): void
    {
        $payment = $this->payments()->first();
        $item = $payment->items()->first();

        $this->package?->dispatchCommands('expiration', $item);

        $this->update(['status' => 'expired']);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['active', 'canceled'])->where('ends_at', '>', now());
    }

    public function scopePendingExpiration(Builder $query): void
    {
        $query->whereIn('status', ['active', 'canceled'])->where('ends_at', '<', now());
    }

    public function scopeNotPending(Builder $query): void
    {
        $query->where('status', '!=', 'pending');
    }

    public function scopeWaitingForRenewal(Builder $query): void
    {
        $query->where('status', 'active')
            ->where('gateway_type', 'azuriom')
            ->where('ends_at', '<', now());
    }
}
