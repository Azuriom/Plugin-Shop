<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Server;
use Azuriom\Models\Traits\HasImage;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Events\PackageDelivered;
use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Concerns\IsBuyable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $short_description
 * @property string $description
 * @property string|null $image
 * @property int $position
 * @property float $price
 * @property array $required_packages
 * @property bool $has_quantity
 * @property array $commands
 * @property bool $need_online
 * @property int $user_limit
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Azuriom\Plugin\Shop\Models\Category $category
 * @property \Illuminate\Support\Collection|\Azuriom\Models\Server[] $servers
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Package extends Model implements Buyable
{
    use IsBuyable;
    use HasImage;
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
        'category_id', 'name', 'short_description', 'description', 'image', 'position', 'price', 'required_packages',
        'has_quantity', 'commands', 'need_online', 'user_limit', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'commands' => 'array',
        'required_packages' => 'array',
        'has_quantity' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the category of this package.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class, 'shop_package_server');
    }

    public function getDescription()
    {
        return $this->short_description;
    }

    /**
     * Scope a query to only include enabled packages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', true)->orderBy('position');
    }

    public function deliver(User $user, int $quantity = 1)
    {
        foreach ($this->servers as $server) {
            for ($i = 0; $i < $quantity; $i++) {
                $server->bridge()->executeCommands($this->commands, $user->name, $this->need_online);
            }
        }

        event(new PackageDelivered($this, $quantity));
    }
}
