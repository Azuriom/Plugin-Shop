<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Server;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Shop\Notifications\GiftcardPurchased;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property float $min_spend
 * @property string|null $icon
 * @property string $type
 * @property array|null $reward_data
 * @property array|null $commands
 * @property int $position
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Models\User[] $users
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Tier extends Model
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
        'name',
        'min_spend',
        'icon',
        'type',
        'reward_data',
        'commands',
        'position',
        'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_spend' => 'float',
        'reward_data' => 'array',
        'commands' => 'array',
        'position' => 'int',
        'is_enabled' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'shop_tier_user')
            ->withPivot('unlocked_at', 'reward_details')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include enabled tiers.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true);
    }

    public static function getUserTotalSpent(User $user): float
    {
        $resetAt = setting('shop.tiers_reset_at');

        $query = Payment::whereBelongsTo($user)->scopes('completed');

        if ($resetAt) {
            $query->where('created_at', '>=', $resetAt);
        }

        if (use_site_money() && setting('shop.tiers_use_site_money', false)) {
            $query->scopes('withSiteMoney');
        } else {
            $query->scopes('withRealMoney');
        }

        return (float) $query->sum('price');
    }

    public static function checkUserProgression(User $user): void
    {
        $tiers = self::enabled()->orderBy('min_spend')->orderBy('position')->get();

        if ($tiers->isEmpty()) {
            return;
        }

        $totalSpent = self::getUserTotalSpent($user);
        $unlockedTierIds = DB::table('shop_tier_user')
            ->where('user_id', $user->id)
            ->pluck('tier_id')
            ->all();

        foreach ($tiers as $tier) {
            if ($totalSpent >= $tier->min_spend && ! in_array($tier->id, $unlockedTierIds, true)) {
                $tier->unlockForUser($user);
                $unlockedTierIds[] = $tier->id;
            }
        }
    }

    public function unlockForUser(User $user): array
    {
        $rewardDetails = $this->deliverReward($user);

        DB::table('shop_tier_user')->insert([
            'tier_id' => $this->id,
            'user_id' => $user->id,
            'unlocked_at' => now(),
            'reward_details' => ! empty($rewardDetails) ? json_encode($rewardDetails) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notificationText = trans('shop::messages.tiers.unlocked', ['tier' => $this->name]);

        if (isset($rewardDetails['code'])) {
            $notificationText .= ' ('.$rewardDetails['code'].')';
        }

        (new AlertNotification($notificationText))->send($user);

        return $rewardDetails;
    }

    public function deliverReward(User $user): array
    {
        $details = [];

        if ($this->type === 'commands') {
            $this->dispatchCommands($user);
            $details['commands_executed'] = true;
        } elseif ($this->type === 'coupon') {
            $discount = (float) Arr::get($this->reward_data, 'discount', 10);
            $isFixed = (bool) Arr::get($this->reward_data, 'is_fixed', false);
            $durationDays = Arr::get($this->reward_data, 'duration_days');

            $code = 'TIER-'.strtoupper(Str::random(8));

            Coupon::create([
                'code' => $code,
                'discount' => $discount,
                'is_fixed' => $isFixed,
                'user_limit' => 1,
                'global_limit' => 1,
                'can_cumulate' => true,
                'is_enabled' => true,
                'is_global' => true,
                'start_at' => now(),
                'expire_at' => $durationDays ? now()->addDays((int) $durationDays) : null,
            ]);

            $details['code'] = $code;
            $details['discount'] = $discount;
            $details['is_fixed'] = $isFixed;
        } elseif ($this->type === 'giftcard') {
            $balance = (float) Arr::get($this->reward_data, 'balance', 5);

            $giftcard = Giftcard::create([
                'code' => Giftcard::randomCode(),
                'balance' => $balance,
                'original_balance' => $balance,
                'start_at' => now(),
                'expire_at' => now()->addYear(),
            ]);

            $giftcard->notifyUser($user);

            $details['code'] = $giftcard->code;
            $details['balance'] = $balance;
        } elseif ($this->type === 'money') {
            $amount = (float) Arr::get($this->reward_data, 'amount', 0);
            if ($amount > 0) {
                $user->addMoney($amount);
                $details['amount'] = $amount;
            }
        }

        return $details;
    }

    public function dispatchCommands(User $user): void
    {
        $commands = collect($this->commands)
            ->filter(fn (mixed $command) => is_array($command))
            ->filter(fn (array $command) => ! empty($command['server']));

        if ($commands->isEmpty()) {
            return;
        }

        $servers = Server::findMany($commands->pluck('server')->unique());

        foreach ($servers as $server) {
            $serverCommands = $commands->where('server', $server->id);

            $onlineCommands = $this->mapCommands($serverCommands, true, $user);
            $offlineCommands = $this->mapCommands($serverCommands, false, $user);

            if (! empty($onlineCommands)) {
                $server->bridge()->sendCommands($onlineCommands, $user, true);
            }

            if (! empty($offlineCommands)) {
                $server->bridge()->sendCommands($offlineCommands, $user, false);
            }
        }
    }

    protected function mapCommands(Collection $commands, bool $onlineOnly, User $user): array
    {
        return $commands->filter(fn (array $command) => ((bool) ($command['require_online'] ?? false)) === $onlineOnly)
            ->pluck('commands')
            ->flatten()
            ->map(fn (string $command) => str_replace([
                '{player}', '{tier_name}', '{min_spend}',
            ], [
                $user->name, $this->name, $this->min_spend,
            ], $command))
            ->all();
    }

    public function isBootstrapIcon(): bool
    {
        if (empty($this->icon)) {
            return false;
        }

        return str_starts_with($this->icon, 'bi ')
            || str_starts_with($this->icon, 'bi-')
            || str_starts_with($this->icon, 'fa ')
            || str_starts_with($this->icon, 'fas ')
            || str_starts_with($this->icon, 'fab ');
    }

    public function iconHtml(string $extraClasses = ''): string
    {
        if (empty($this->icon)) {
            return '';
        }

        if ($this->isBootstrapIcon()) {
            return '<i class="'.$this->icon.($extraClasses !== '' ? ' '.$extraClasses : '').'"></i>';
        }

        return '<span'.($extraClasses !== '' ? ' class="'.$extraClasses.'"' : '').'>'.$this->icon.'</span>';
    }
}

