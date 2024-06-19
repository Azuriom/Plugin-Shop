<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $name
 * @property float $price
 * @property int $quantity
 * @property string $buyable_type
 * @property array $variables
 * @property int $buyable_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $expires_at
 * @property \Azuriom\Plugin\Shop\Models\Payment $payment
 * @property \Azuriom\Plugin\Shop\Models\Package|\Azuriom\Plugin\Shop\Models\Offer|null $buyable
 *
 * @method static \Illuminate\Database\Eloquent\Builder excludeExpired()
 */
class PaymentItem extends Model
{
    use HasTablePrefix;

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
        'name', 'price', 'quantity', 'variables', 'expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'variables' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if ($item->buyable?->billing_period !== null) {
                $item->expires_at = $item->freshTimestamp()->add($item->buyable->billing_period);
            }
        });
    }

    /**
     * Get the payment associated to this payment item.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the purchased model.
     */
    public function buyable()
    {
        return $this->morphTo('buyable');
    }

    public function deliver(bool $renewal = false): void
    {
        $this->buyable?->deliver($this, $renewal);
    }

    public function expire(): void
    {
        $this->dispatchCommands('expiration');

        if ($this->buyable instanceof Package) {
            $this->buyable->expire($this);
        }

        $this->update(['expires_at' => null]);
    }

    public function dispatchCommands(string $status): void
    {
        if ($this->buyable instanceof Package) {
            $this->buyable->dispatchCommands($status, $this);
        }
    }

    public function formatPrice(): string
    {
        $currency = $this->payment->isWithSiteMoney()
            ? money_name($this->price)
            : currency_display($this->payment->currency);

        return $this->price.' '.$currency;
    }

    public function replaceVariables(string $content): string
    {
        if ($this->variables === null) {
            return $content;
        }

        $search = array_map(fn (string $key) => '{'.$key.'}', array_keys($this->variables));

        return str_replace($search, $this->variables, $content);
    }

    public function scopeExcludeExpired(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
