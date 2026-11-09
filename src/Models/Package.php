<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Role;
use Azuriom\Models\Traits\HasImage;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Shop\Enums\PurchaseStatus;
use Azuriom\Plugin\Shop\Events\PackageDelivered;
use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Concerns\DispatchCommands;
use Azuriom\Plugin\Shop\Models\Concerns\IsBuyable;
use Azuriom\Plugin\Shop\Models\Concerns\ManageFiles;
use Azuriom\Plugin\Shop\Models\User as ShopUser;
use Azuriom\Plugin\Shop\Notifications\CodesDelivered;
use Azuriom\Plugin\Shop\Notifications\FileDownload;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $short_description
 * @property string $description
 * @property string|null $image
 * @property int $position
 * @property float $price
 * @property string $billing_type
 * @property string|null $billing_period
 * @property \Illuminate\Support\Collection|null $required_packages
 * @property \Illuminate\Support\Collection|null $required_roles
 * @property bool $has_quantity
 * @property array $commands
 * @property array $files
 * @property int|null $role_id
 * @property int|null $expired_role_id
 * @property float|null $money
 * @property float|null $giftcard_balance
 * @property bool $custom_price
 * @property int $user_limit
 * @property string|null $user_limit_period
 * @property int $global_limit
 * @property string|null $global_limit_period
 * @property bool $limits_no_expired
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Plugin\Shop\Models\Category $category
 * @property \Azuriom\Models\Role|null $role
 * @property \Azuriom\Models\Role|null $expiredRole
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Discount[] $discounts
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Variable[] $variables
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Subscription[] $subscriptions
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\PackageCode[] $codes
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Package extends Model implements Buyable
{
    use DispatchCommands;
    use HasImage;
    use HasTablePrefix;
    use IsBuyable;
    use Loggable;
    use ManageFiles;

    public const COMMAND_TRIGGERS = ['purchase', 'renewal', 'expiration', 'refund', 'chargeback'];

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
        'position', 'price', 'billing_type', 'billing_period', 'required_packages',
        'required_roles', 'has_quantity', 'commands', 'files', 'role_id', 'expired_role_id',
        'money', 'giftcard_balance', 'custom_price', 'user_limit', 'user_limit_period',
        'global_limit', 'global_limit_period', 'limits_no_expired', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'commands' => 'array',
        'files' => 'array',
        'money' => 'float',
        'giftcard_balance' => 'float',
        'required_packages' => 'collection',
        'required_roles' => 'collection',
        'custom_price' => 'boolean',
        'has_quantity' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    protected static function booted()
    {
        // Codes already given are kept, as they still belong to the buyers
        static::deleting(function (self $package) {
            $package->codes()->scopes('available')->delete();
        });

        static::deleted(function (self $package) {
            GatewayMetadata::whereMorphedTo('model', $package)->delete();
        });
    }

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

    public function expiredRole()
    {
        return $this->belongsTo(Role::class, 'expired_role_id');
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'shop_discount_package');
    }

    public function variables()
    {
        return $this->belongsToMany(Variable::class, 'shop_package_variable');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function codes()
    {
        return $this->hasMany(PackageCode::class);
    }

    public function getDescription(): string
    {
        return $this->short_description;
    }

    public function hasGiftcard(): bool
    {
        return $this->giftcard_balance !== null;
    }

    public function isSubscription()
    {
        return $this->billing_type === 'subscription';
    }

    public function subscriptionPeriodCount(): int
    {
        if ($this->billing_period === null) {
            return 0;
        }

        return (int) Str::before($this->billing_period, ' ');
    }

    public function subscriptionPeriodUnit(): string
    {
        return Str::after($this->billing_period ?? '', ' ');
    }

    public function isUserSubscribed(?User $user = null)
    {
        $user ??= shop_user();

        if (! $this->isSubscription() || $user === null) {
            return false;
        }

        return ShopUser::activeSubscriptionPackages($user)->contains($this->id);
    }

    public function getPrice(): float
    {
        $role = shop_user()?->role;

        $price = $this->discounts
            ->where('is_global', false)
            ->merge(Discount::getGlobalDiscounts())
            ->filter(fn (Discount $discount) => $discount->isActive())
            ->filter(fn (Discount $discount) => $discount->activeForRole($role))
            ->reduce(function ($result, Discount $discount) {
                return $result - ($discount->discount / 100) * $result;
            }, max($this->price - $this->getCumulatedPurchasesTotal(), 0));

        return round(max($price, 0), 2);
    }

    protected function getCumulatedPurchasesTotal(): float
    {
        if (! $this->category->cumulate_purchases || $this->isSubscription()) {
            return 0;
        }

        $purchasedPackage = $this->category->packages
            ->filter(fn (self $package) => $package->price < $this->price)
            ->sortByDesc('price')
            ->first(fn (self $package) => $package->countUserPurchases() > 0);

        return $purchasedPackage->price ?? 0;
    }

    protected function cumulativePurchasesBlocked(): bool
    {
        if (! $this->category->cumulate_strict) {
            return false;
        }

        return $this->category->packages
            ->filter(fn (self $package) => $package->price >= $this->price)
            ->first(fn (self $package) => $package->countUserPurchases() > 0) !== null;
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

        $missing = $this->required_packages->diff(ShopUser::currentUserPurchases()->pluck('buyable_id'));

        // Required packages that have been deleted are ignored
        return $missing->isEmpty() || self::whereKey($missing)->doesntExist();
    }

    /**
     * Get the total purchases for this package for the current user.
     */
    public function countUserPurchases(?DateTime $start = null, bool $noExpired = false): int
    {
        $purchases = ShopUser::currentUserPurchases();

        if ($purchases->isEmpty()) {
            return 0;
        }

        return $purchases
            ->when($start, function (Collection $items) use ($start) {
                return $items->filter(fn (PaymentItem $item) => $item->created_at->isAfter($start));
            })
            ->when($noExpired, function (Collection $items) {
                return $items->reject(fn (PaymentItem $item) => $item->isExpired());
            })
            ->groupBy('buyable_id')
            ->map(fn (Collection $items) => $items->sum('quantity'))
            ->get($this->id, 0);
    }

    /**
     * Get the total purchases for this package, for all users.
     */
    public function countTotalPurchases(?DateTime $start = null, bool $noExpired = false): int
    {
        $cached = once(fn () => collect([]));
        $cacheKey = $start?->format('Y-m-d').$noExpired;

        if ($cached->has($cacheKey)) {
            return $cached->get($cacheKey);
        }

        $totalPurchases = PaymentItem::where('buyable_id', $this->id)
            ->where('buyable_type', 'shop.packages')
            ->when($noExpired, fn (Builder $q) => $q->scopes('excludeExpired'))
            ->whereHas('payment', function (Builder $query) use ($start) {
                $query->where('status', 'completed')
                    ->when($start, function () use ($query, $start) {
                        $query->where('created_at', '>', $start);
                    });
            })
            ->sum('quantity');

        $cached->put($cacheKey, $totalPurchases);

        return $totalPurchases;
    }

    /**
     * Get the price with the price changes of the selected dropdown options added together.
     *
     * @param  array<string, string>  $selectedValues  the value selected by the user, by variable name
     */
    public function getPriceWithVariables(array $selectedValues): float
    {
        $changes = $this->getVariablesPrices($selectedValues);

        return round(max($this->getPrice() + array_sum($changes), 0), 2);
    }

    /**
     * Get the price change of each selected dropdown option, by variable name.
     * Variables without a selected option or price change are excluded.
     *
     * @param  array<string, string>  $selectedValues  the value selected by the user, by variable name
     * @return array<string, float>
     */
    public function getVariablesPrices(array $selectedValues): array
    {
        $price = $this->getPrice();

        return $this->variables
            ->mapWithKeys(function (Variable $variable) use ($selectedValues, $price) {
                $option = $variable->selectedOption($selectedValues[$variable->name] ?? null);

                return [$variable->name => $option !== null ? $variable->priceChange($option, $price) : 0.0];
            })
            ->filter()
            ->all();
    }

    public function getOriginalPrice(): float
    {
        return $this->price;
    }

    public function getMaxQuantity(): int
    {
        if ($this->global_limit === 0 || $this->cumulativePurchasesBlocked()) {
            return 0;
        }

        $userStart = optional($this->user_limit_period, fn ($period) => now()->sub($period));
        $globalStart = optional($this->global_limit_period, fn ($period) => now()->sub($period));
        $noExpired = $this->limits_no_expired;

        $user = $this->user_limit !== null
            ? $this->user_limit - $this->countUserPurchases($userStart, $noExpired)
            : 100;
        $global = $this->global_limit !== null
            ? $this->global_limit - $this->countTotalPurchases($globalStart, $noExpired)
            : 100;

        return max(min($user, $global, $this->availableCodesCount() ?? 100), 0);
    }

    /**
     * Get the number of codes that can still be given to buyers,
     * or null if this package doesn't give codes.
     */
    public function availableCodesCount(): ?int
    {
        // Use the counts when they are already loaded, see scope 'withCodesCount'
        $total = $this->hasAttribute('codes_count')
            ? $this->codes_count : $this->codes()->count();

        if ((int) $total === 0) {
            return null;
        }

        return (int) ($this->hasAttribute('available_codes_count')
            ? $this->available_codes_count : $this->codes()->scopes('available')->count());
    }

    /**
     * Get whether the current user can purchase this package, or why it can't.
     */
    public function purchaseStatus(): PurchaseStatus
    {
        $user = shop_user();

        if ($this->isSubscription()) {
            if ($this->isUserSubscribed($user)) {
                return PurchaseStatus::Subscribed;
            }
        } elseif ($this->global_limit === 0 || $this->availableCodesCount() === 0) {
            return PurchaseStatus::Unavailable;
        } elseif ($this->getMaxQuantity() < 1 || ($user !== null && $this->category->hasReachLimit($user))) {
            return PurchaseStatus::Limit;
        }

        // Requirements depend on the user, so they are only checked once logged in
        if ($user !== null && (! $this->hasBoughtRequirements() || ! $this->hasRequiredRole($user->role))) {
            return PurchaseStatus::Requirements;
        }

        return PurchaseStatus::Available;
    }

    /**
     * Add the given codes, to give one to each buyer.
     */
    public function addCodes(array $codes): void
    {
        $this->codes()->createMany(array_map(fn (string $code) => ['code' => $code], $codes));
    }

    public function isInCart(): bool
    {
        return shop_cart()->has($this);
    }

    public function isDiscounted(): bool
    {
        return $this->getPrice() !== $this->getOriginalPrice();
    }

    public function deliver(PaymentItem $item, bool $renewal = false): void
    {
        $user = $item->payment->user;

        $this->dispatchCommands($renewal ? 'renewal' : 'purchase', $item);

        if ($this->role !== null && ! $this->role->is_admin && $user->role->power < $this->role->power) {
            $user->role()->associate($this->role)->save();
        }

        if ($this->money > 0) {
            $user->addMoney($this->money * $item->quantity);
        }

        if ($this->hasGiftcard()) {
            $balance = $this->giftcard_balance > 0
                ? $this->giftcard_balance
                : $item->price;

            for ($i = 0; $i < $item->quantity; $i++) {
                Giftcard::generate($balance)->notifyUser($user);
            }
        }

        if (! $renewal) {
            $this->sendCodes($item, $user);
        }

        $this->sendFiles($user);

        event(new PackageDelivered($user, $this, $item->quantity));
    }

    public function sendCodes(PaymentItem $item, User $user): void
    {
        if (! $this->codes()->exists()) {
            return;
        }

        $codes = $this->codes()
            ->scopes('available')
            ->orderBy('id')
            ->limit($item->quantity)
            ->get()
            ->filter(fn (PackageCode $code) => $code->assignTo($item));

        if ($codes->count() < $item->quantity) {
            logger()->warning("[Shop] Not enough codes available for package #{$this->id} (payment item #{$item->id})");
        }

        if ($codes->isEmpty()) {
            return;
        }

        (new AlertNotification(trans('shop::messages.packages.codes', [
            'package' => $this->name,
        ])))
            ->link(route('shop.profile', [], false))
            ->send($user);

        rescue(fn () => $user->notify(new CodesDelivered($this, $codes->pluck('code')->all())));
    }

    public function sendFiles(User $user): void
    {
        foreach ($this->files ?? [] as $file => $fileName) {
            $url = route('shop.packages.file', [$this, $file]);

            (new AlertNotification(trans('shop::messages.packages.file', [
                'file' => $fileName,
            ])))
                ->link(route('shop.packages.file', [$this, $file], false))
                ->send($user);

            $user->notify(new FileDownload($user, $url));
        }
    }

    public function expire(PaymentItem $item, string $trigger = 'expiration'): void
    {
        $this->dispatchCommands($trigger, $item);

        if ($this->expiredRole === null || $this->expiredRole->is_admin) {
            return;
        }

        $user = $item->payment->user;

        if (! $user->isAdmin()) {
            $user->role()->associate($this->expiredRole)->save();
        }
    }

    /**
     * Scope a query to include the codes counts used by availableCodesCount(),
     * to avoid queries for each package when displaying many packages.
     */
    public function scopeWithCodesCount(Builder $query): void
    {
        $query->withCount([
            'codes',
            'codes as available_codes_count' => fn (Builder $query) => $query->scopes('available'),
        ]);
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
        $commands = collect($this->commands)
            ->merge(($json = setting('shop.commands')) ? json_decode($json, true) : [])
            ->filter(fn (mixed $command) => is_array($command))
            ->filter(fn (array $command) => $command['trigger'] === $trigger)
            ->map(function (array $command) use ($item) {
                $server = $command['server'];

                if (! is_numeric($server)) {
                    $command['server'] = Arr::get($item->variables ?? [], $server);
                }

                $times = ($command['ignore_quantity'] ?? false) ? 1 : $item->quantity;

                $command['commands'] = collect(array_fill(0, $times, $command['commands']))
                    ->flatten()
                    ->map(fn (string $command) => str_replace([
                        '{quantity}', '{package_id}', '{package_name}', '{price}', '{transaction_id}',
                    ], [
                        $item->quantity, $this->id, $this->name, $item->price, $item->payment->transaction_id,
                    ], $command))
                    ->map(fn (string $command) => $item->replaceVariables($command))
                    ->all();

                return $command;
            })
            ->filter(fn (array $command) => $command['server'] !== null);

        $this->sendServerCommands($commands, $item->payment->user);
    }
}
