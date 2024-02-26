<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Role;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
 * @property \Illuminate\Support\Collection|null $required_roles
 * @property bool $has_quantity
 * @property array $commands
 * @property int|null $role_id
 * @property float|null $money
 * @property float|null $giftcard_balance
 * @property bool $custom_price
 * @property int $user_limit
 * @property int $global_limit
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Plugin\Shop\Models\Category $category
 * @property \Azuriom\Models\Role|null $role
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Discount[] $discounts
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Package extends Model implements Buyable
{
    use HasImage;
    use HasTablePrefix;
    use IsBuyable;
    use Loggable;

    public const COMMAND_TRIGGERS = ['purchase', 'refund', 'chargeback'];

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
        'category_id', 'name', 'short_description', 'description', 'image',
        'position', 'price', 'required_packages', 'required_roles', 'has_quantity',
        'commands', 'role_id', 'money', 'giftcard_balance', 'custom_price',
        'user_limit', 'global_limit', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'commands' => 'array',
        'money' => 'float',
        'giftcard_balance' => 'float',
        'required_packages' => 'collection',
        'required_roles' => 'collection',
        'custom_price' => 'boolean',
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'shop_discount_package');
    }

    public function getDescription(): string
    {
        return $this->short_description;
    }

    public function getPrice(): float
    {
        static $globalDiscounts = null;

        if ($globalDiscounts === null) {
            $globalDiscounts = Discount::scopes(['active', 'global'])->get();
        }

        $price = $this->discounts
            ->where('is_global', false)
            ->merge($globalDiscounts)
            ->filter(fn (Discount $discount) => $discount->isActive())
            ->reduce(function ($result, Discount $discount) {
                return $result - ($discount->discount / 100) * $result;
            }, $this->price - $this->getCumulatedPurchasesTotal());

        return round(max($price, 0), 2);
    }

    protected function getCumulatedPurchasesTotal(): float
    {
        if (! $this->category->cumulate_purchases) {
            return 0;
        }

        $purchasedPackage = $this->category->packages
            ->filter(fn (self $package) => $package->price < $this->price)
            ->sortByDesc('price')
            ->first(fn (self $package) => $package->countUserPurchases() > 0);

        return $purchasedPackage->price ?? 0;
    }

    public function hasRequiredRole(Role $role): bool
    {
        if ($this->required_roles === null || $this->required_roles->isEmpty()) {
            return true;
        }

        return $role->is_admin || $this->required_roles->contains($role->id);
    }

    public function hasBoughtRequirements(): bool
    {
        if ($this->required_packages === null || $this->required_packages->isEmpty()) {
            return true;
        }

        $packages = self::findMany($this->required_packages);

        return ! $packages->contains(function (self $package) {
            return $package->countUserPurchases() < 1;
        });
    }

    /**
     * Get the total purchases for this package for the current user.
     */
    public function countUserPurchases(): int
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
                ->groupBy('buyable_id')
                ->map(fn (Collection $items) => $items->sum('quantity'));
        }

        return $purchases->get($this->id, 0);
    }

    /**
     * Get the total purchases for this package for the current user.
     */
    public function countTotalPurchases(): int
    {
        static $purchases = [];

        return Arr::get($purchases, $this->id, function () use (&$purchases) {
            return $purchases[$this->id] = PaymentItem::where('buyable_id', $this->id)
                ->where('buyable_type', 'shop.packages')
                ->whereHas('payment', function (Builder $query) {
                    $query->where('status', 'completed');
                })
                ->sum('quantity');
        });
    }

    public function getOriginalPrice(): float
    {
        return $this->price;
    }

    public function getMaxQuantity(): int
    {
        $user = $this->user_limit > 0
            ? $this->user_limit - $this->countUserPurchases() : 100;
        $global = $this->global_limit > 0
            ? $this->global_limit - $this->countTotalPurchases() : 100;

        return max(min($user, $global), 0);
    }

    public function isInCart(): bool
    {
        return shop_cart()->has($this);
    }

    public function isDiscounted(): bool
    {
        return $this->getPrice() !== $this->getOriginalPrice();
    }

    public function deliver(PaymentItem $item): void
    {
        $user = $item->payment->user;

        $this->dispatchCommands('purchase', $item);

        if ($this->role !== null && ! $this->role->is_admin && $user->role->power < $this->role->power) {
            $user->role()->associate($this->role)->save();
        }

        if ($this->money > 0) {
            $user->addMoney($this->money);
        }

        if ($this->giftcard_balance > 0) {
            $giftcard = Giftcard::create([
                'code' => Giftcard::randomCode(),
                'balance' => $this->giftcard_balance,
                'original_balance' => $this->giftcard_balance,
                'start_at' => now(),
                'expire_at' => now()->addYear(),
            ]);

            $giftcard->notifyUser($user);
        }

        event(new PackageDelivered($user, $this, $item->quantity));
    }

    /**
     * Scope a query to only include enabled packages.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true)->orderBy('position');
    }

    public function dispatchCommands(string $trigger, PaymentItem $item): void
    {
        $user = $item->payment->user;

        $commandsByServer = collect($this->commands)
            ->merge(($json = setting('shop.commands')) ? json_decode($json, true) : [])
            ->filter(fn (mixed $command) => is_array($command))
            ->filter(fn (array $command) => $command['trigger'] === $trigger)
            ->groupBy('server');

        $servers = Server::findMany($commandsByServer->keys());

        foreach ($servers as $server) {
            $commands = $commandsByServer[$server->id];

            $onlineCommands = $this->mapCommands($commands, true, $item);
            $offlineCommands = $this->mapCommands($commands, false, $item);

            if (! empty($onlineCommands)) {
                $server->bridge()->sendCommands($onlineCommands, $user, true);
            }

            if (! empty($offlineCommands)) {
                $server->bridge()->sendCommands($offlineCommands, $user, false);
            }
        }
    }

    protected function mapCommands(Collection $commands, bool $onlineOnly, PaymentItem $item): array
    {
        $commands = $commands->filter(fn (array $command) => ((bool) $command['require_online']) === $onlineOnly);

        return $commands->pluck('command')
            ->map(fn (string $command) => str_replace([
                '{quantity}', '{package_id}', '{package_name}', '{price}', '{transaction_id}',
            ], [
                $item->quantity, $this->id, $this->name, $item->price, $item->payment->transaction_id,
            ], $command))
            ->all();
    }
}
