<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Server;
use Azuriom\Models\Traits\HasImage;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Events\PackageDelivered;
use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Concerns\IsBuyable;
use Azuriom\Plugin\Shop\Models\User as ShopUser;
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
 * @property \Illuminate\Support\Collection|null $required_packages
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
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Discount[] $discounts
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Package extends Model implements Buyable
{
    use IsBuyable;
    use HasImage;
    use HasTablePrefix;
    use Loggable;

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
        'price' => 'float',
        'commands' => 'array',
        'required_packages' => 'collection',
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

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'shop_discount_package');
    }

    public function getDescription()
    {
        return $this->short_description;
    }

    public function getPrice()
    {
        static $globalDiscounts = null;

        if ($globalDiscounts === null) {
            $globalDiscounts = Discount::scopes(['active', 'global'])->get();
        }

        $price = $this->discounts
            ->where('is_global', false)
            ->merge($globalDiscounts)
            ->filter(function (Discount $discount) {
                return $discount->isActive();
            })->reduce(function ($result, Discount $discount) {
                return $result - ($discount->discount / 100) * $result;
            }, $this->price - $this->getCumulatedPurchasesTotal());

        return round(max($price, 0), 2);
    }

    protected function getCumulatedPurchasesTotal()
    {
        if (! $this->category->cumulate_purchases) {
            return 0;
        }

        $purchasedPackage = $this->category->packages
            ->filter(function (Package $package) {
                return $package->price < $this->price;
            })
            ->sortByDesc('price')
            ->first(function (Package $package) {
                return $package->getUserTotalPurchases() > 0;
            });

        return $purchasedPackage->price ?? 0;
    }

    public function hasBoughtRequirements()
    {
        if ($this->required_packages === null || $this->required_packages->isEmpty()) {
            return true;
        }

        $packages = self::findMany($this->required_packages);

        return ! $packages->contains(function (Package $package) {
            return $package->getUserTotalPurchases() < 1;
        });
    }

    /**
     * Get the total purchases for this package for the current user.
     *
     * @return int
     */
    public function getUserTotalPurchases()
    {
        if (auth()->guest()) {
            return 0;
        }

        static $purchases = null;

        if ($purchases === null) {
            $purchases = ShopUser::ofUser(auth()->user())
                ->items()
                ->where('shop_payments.status', 'completed')
                ->where('shop_payment_items.buyable_type', 'shop.packages')
                ->get()
                ->countBy('buyable_id');
        }

        return $purchases->get($this->id, 0);
    }

    public function getRemainingUserPurchases()
    {
        return max($this->getMaxQuantity() - $this->getUserTotalPurchases(), 0);
    }

    public function getOriginalPrice()
    {
        return $this->price;
    }

    public function getMaxQuantity()
    {
        if ($this->user_limit < 1) {
            return 100;
        }

        return max($this->user_limit - $this->getUserTotalPurchases(), 0);
    }

    public function isInCart()
    {
        return shop_cart()->has($this);
    }

    public function isDiscounted()
    {
        return $this->getPrice() !== $this->getOriginalPrice();
    }

    public function deliver(User $user, int $quantity = 1)
    {
        foreach ($this->servers as $server) {
            $commands = $this->getCommandsToDispatch($quantity);

            $server->bridge()->executeCommands($commands, $user->name, $this->need_online);
        }

        event(new PackageDelivered($user, $this, $quantity));
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

    protected function getCommandsToDispatch(int $quantity)
    {
        $commands = [];

        for ($i = 0; $i < $quantity; $i++) {
            $commands[] = $this->commands;
        }

        if ($globalCommands = setting('shop.commands')) {
            $commands[] = json_decode($globalCommands);
        }

        return array_map(function (string $command) use ($quantity) {
            return str_replace([
                '{quantity}',
                '{package_id}',
                '{package_name}',
            ], [$quantity, $this->id, $this->name], $command);
        }, array_merge([], ...$commands));
    }
}
