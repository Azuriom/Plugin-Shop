<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $code
 * @property int $amount
 * @property int $used
 * @property int $max_usage
 * @property bool $is_enabled
 * @property bool $is_global
 * @property \Carbon\Carbon|null $start_at
 * @property \Carbon\Carbon|null $expire_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Payment[] $payments
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
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
        'code', 'amount', 'start_at', 'expire_at', 'global_limit', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'expire_at' => 'datetime',
        'is_enabled' => 'boolean',
    ];

    public function hasReachLimit(User $user)
    {
        return $this->hasReachGlobalLimit() || $this->hasReachUserLimit($user);
    }

    protected function hasReachUserLimit(User $user)
    {
        return DB::table('shop_giftcards_user')->where('user_id', $user->id)->exists();
    }

    protected function hasReachGlobalLimit()
    {
        if (! $this->max_usage) {
            return false;
        }

        $count = DB::table('shop_giftcards_user')
            ->select('user_id')->where('giftcard_id', $this->id)->count();

        return $count >= $this->global_limit;
    }

    /**
     * Determine if this giftcard is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_enabled && $this->start_at->isPast() && $this->expire_at->isFuture();
    }

    /**
     * Scope a query to only include active giftcards.
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
     * Scope a query to only include enabled giftcards.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', true);
    }
}
