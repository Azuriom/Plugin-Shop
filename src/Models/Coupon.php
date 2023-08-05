<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property int $discount
 * @property int $user_limit
 * @property int $global_limit
 * @property bool $can_cumulate
 * @property bool $is_enabled
 * @property bool $is_global
 * @property bool $is_fixed
 * @property \Carbon\Carbon|null $start_at
 * @property \Carbon\Carbon|null $expire_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Payment[] $payments
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Coupon extends Model
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
        'code', 'discount', 'start_at', 'expire_at', 'user_limit', 'global_limit', 'can_cumulate', 'is_enabled', 'is_global', 'is_fixed',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_at' => 'datetime',
        'expire_at' => 'datetime',
        'can_cumulate' => 'boolean',
        'is_enabled' => 'boolean',
        'is_global' => 'boolean',
        'is_fixed' => 'boolean',
    ];

    /**
     * Get the packages on which this coupon code is effective.
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'shop_coupon_package');
    }

    /**
     * Get the payments made with this coupon.
     */
    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'shop_coupon_payment');
    }

    public function hasReachLimit(User $user): bool
    {
        return $this->hasReachGlobalLimit() || $this->hasReachUserLimit($user);
    }

    protected function hasReachUserLimit(User $user): bool
    {
        if (! $this->user_limit) {
            return false;
        }

        $count = $this->payments()
            ->scopes('completed')
            ->where('user_id', $user->id)
            ->count();

        return $count >= $this->user_limit;
    }

    public function hasReachGlobalLimit(): bool
    {
        if (! $this->global_limit) {
            return false;
        }

        $count = $this->relationLoaded('payments')
            ? $this->payments->filter(fn ($payment) => $payment->isCompleted())->count()
            : $this->payments()->scopes('completed')->count();

        return $count >= $this->global_limit;
    }

    /**
     * Determine if this coupon is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_enabled && $this->start_at->isPast() && $this->expire_at->isFuture();
    }

    /**
     * Determine if this coupon is currently active and can be used on the given package.
     */
    public function isActiveOn(Package $package): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->is_global) {
            return true;
        }

        return $this->packages->contains($package);
    }

    /**
     * Get the discount amount for this coupon.
     */
    public function applyOn(float $price): float
    {
        $discount = $this->is_fixed ? $this->discount : (($this->discount / 100) * $price);

        return max($price - $discount, 0);
    }

    /**
     * Scope a query to only include active coupons.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_enabled', true)
            ->where('start_at', '<', now())
            ->where('expire_at', '>', now());
    }

    /**
     * Scope a query to only include enabled coupons.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true);
    }
}
