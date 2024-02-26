<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Shop\Notifications\GiftcardPurchased;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
     */
    protected string $prefix = 'shop_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code', 'balance', 'original_balance', 'start_at', 'expire_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
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
     */
    public function isActive(): bool
    {
        return $this->balance > 0 && $this->start_at->isPast() && $this->expire_at->isFuture();
    }

    public function isPending()
    {
        return Cache::has('shop.giftcards.pending.'.$this->id);
    }

    public function refreshBalance()
    {
        if ($this->isPending()) {
            return;
        }

        $total = $this->payments()->scopes('notPending')->sum('amount');

        $this->update([
            'balance' => max($this->original_balance - $total, 0),
        ]);
    }

    public function notifyUser(User $user): void
    {
        (new AlertNotification(trans('shop::messages.giftcards.notification', [
            'balance' => shop_format_amount($this->balance),
            'code' => $this->code,
        ])))->send($user);

        rescue(fn () => $user->notify(new GiftcardPurchased($this)));
    }

    /**
     * Scope a query to only include active gift cards.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('balance', '>', 0)
            ->where('start_at', '<', now())
            ->where('expire_at', '>', now());
    }

    public static function randomCode(): string
    {
        return Collection::times(4, function () {
            return Collection::times(4, fn () => random_int(0, 9))->implode('');
        })->implode('-');
    }

    /**
     * Creates an url to the user profile page
     * with the gift card code as query param for easy share.
     */
    public function shareableLink(): string
    {
        return route('shop.profile', ['giftcard' => $this->code]);
    }
}
