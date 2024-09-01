<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Models\User as ShopUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string $slug
 * @property string|null $description
 * @property int $position
 * @property int $parent_id
 * @property bool $cumulate_purchases
 * @property bool $single_purchase
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Plugin\Shop\Models\Category $parent
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Category[] $categories
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 *
 * @method static \Illuminate\Database\Eloquent\Builder parents()
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Category extends Model
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
        'name', 'icon', 'slug', 'description', 'position', 'parent_id',
        'cumulate_purchases', 'single_purchase', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the parent category of this category.
     */
    public function category()
    {
        return $this->belongsTo(self::class, 'parent_id')->orderBy('position');
    }

    /**
     * Get the subcategories in this category.
     */
    public function categories()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    /**
     * Get the packages in this category.
     */
    public function packages()
    {
        return $this->hasMany(Package::class)->orderBy('position');
    }

    public function hasReachLimit(User $user): bool
    {
        if (! $this->single_purchase) {
            return false;
        }

        return ShopUser::ofUser($user)
            ->items()
            ->scopes('excludeExpired')
            ->whereHas('payment', fn (Builder $q) => $q->scopes('completed'))
            ->whereHas('buyable', function (Builder $query) {
                $query->where('category_id', $this->id);
            })
            ->count() > 0;
    }

    /**
     * Scope a query to only include enabled categories.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true)->orderBy('position');
    }

    /**
     * Scope a query to only include parent categories.
     */
    public function scopeParents(Builder $query): void
    {
        $query->whereNull('parent_id')->orderBy('position');
    }
}
