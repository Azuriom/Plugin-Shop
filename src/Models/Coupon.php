<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property int $discount
 * @property bool $is_enabled
 * @property bool $is_global
 * @property \Carbon\Carbon|null $start_at
 * @property \Carbon\Carbon|null $expire_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Coupon extends Model
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
        'code', 'discount', 'start_at', 'expire_at', 'is_enabled', 'is_global',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'packages' => 'array',
        'start_at' => 'datetime',
        'expire_at' => 'datetime',
        'is_enabled' => 'boolean',
        'is_global' => 'boolean',
    ];

    /**
     * Get the packages on which this coupon code is effective.
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'shop_coupon_package');
    }

    /**
     * Determine if this coupon is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_enabled && $this->start_at->isPast() && $this->expire_at->isFuture();
    }

    public function isActiveOn(Package $package)
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
     * Scope a query to only include active coupons.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_enabled', true)
            ->where('start_at', '<', now())
            ->where('expire_at', '>', now());
    }

    /**
     * Scope a query to only include enabled coupons.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', true);
    }
}
