<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Shop\Notifications\GiftcardPurchased;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $code
 * @property float $balance
 * @property float $original_balance
 * @property \Carbon\Carbon|null $start_at
 * @property \Carbon\Carbon|null $expire_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Models\Payment[] $payments
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 */
class Giftcard extends Model
{
    use HasTablePrefix;

    /**
     * The table prefix associated with the model.
     *
     * @var string
     */
    protected $prefix = 'shop_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'balance', 'original_balance', 'start_at', 'expire_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'shop_giftcard_payment')
            ->withPivot('amount');
    }

    /**
     * Determine if this giftcard is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->balance > 0 && $this->start_at->isPast() && $this->expire_at->isFuture();
    }

    /**
     * Scope a query to only include active gift cards.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('balance', '>', 0)
            ->where('start_at', '<', now())
            ->where('expire_at', '>', now());
    }

    public function notifyUser(User $user)
    {
        (new AlertNotification(trans('shop::messages.giftcards.notification', [
            'balance' => shop_format_amount($this->balance),
            'code' => $this->code,
        ])))->send($user);

        rescue(fn () => $user->notify(new GiftcardPurchased($this)));
    }

    public static function randomCode()
    {
        return Collection::times(4, function () {
            return Collection::times(4, fn () => random_int(0, 9))->implode('');
        })->implode('-');
    }
}
