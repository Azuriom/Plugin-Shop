<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $position
 * @property int $parent_id
 * @property bool $cumulate_purchases
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
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
    use HasTranslations;

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
        'name', 'position', 'parent_id', 'cumulate_purchases', 'is_enabled',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = ['name'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
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

    /**
     * Scope a query to only include enabled categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', true)->orderBy('position');
    }

    /**
     * Scope a query to only include parent forums.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParents(Builder $query)
    {
        return $query->whereNull('parent_id')->orderBy('position');
    }
}
