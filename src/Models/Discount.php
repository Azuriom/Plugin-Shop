<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Role;
use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $discount
 * @property array|null $roles
 * @property bool $is_global
 * @property bool $is_enabled
 * @property \Carbon\Carbon $start_at
 * @property \Carbon\Carbon $end_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder global()
 */
class Discount extends Model
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
        'name', 'discount', 'packages', 'roles', 'is_global', 'is_enabled', 'start_at', 'end_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'roles' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_global' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the packages affected by this discount.
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'shop_discount_package');
    }

    /**
     * Determine if this discount is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_enabled && $this->start_at->isPast() && $this->end_at->isFuture();
    }

    /**
     * Determine if this discount is active for the given role.
     */
    public function activeForRole(?Role $role): bool
    {
        if ($this->roles === null) {
            return true;
        }

        return $role !== null && in_array($role->id, $this->roles, true);
    }

    /**
     * Scope a query to only include active discounts.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_enabled', true)
            ->where('start_at', '<', now())
            ->where('end_at', '>', now());
    }

    /**
     * Scope a query to only include enabled discounts.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true);
    }

    /**
     * Scope a query to only include global discounts.
     */
    public function scopeGlobal(Builder $query): void
    {
        $query->where('is_global', true);
    }

    public function setRolesAttribute(?array $roles): void
    {
        $this->attributes['roles'] = optional($roles, function (array $roles) {
            return Json::encode(array_map(fn ($val) => (int) $val, $roles));
        });
    }
}
