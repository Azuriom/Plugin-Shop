<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Notification;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Shop\Models\Concerns\DispatchCommands;
use Azuriom\Plugin\Shop\Notifications\GiftcardPurchased;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property float $amount
 * @property array|null $commands
 * @property float|null $money
 * @property float|null $giftcard_balance
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\GoalCompletion[] $completions
 * @property \Azuriom\Plugin\Shop\Models\GoalCompletion|null $currentCompletion
 */
class GoalTier extends Model
{
    use DispatchCommands;
    use HasTablePrefix;
    use Loggable;

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
        'name', 'icon', 'amount', 'commands', 'money', 'giftcard_balance',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'commands' => 'array',
        'money' => 'float',
        'giftcard_balance' => 'float',
    ];

    /**
     * Get the completions of this tier, one per month it was reached.
     */
    public function completions()
    {
        return $this->hasMany(GoalCompletion::class, 'tier_id');
    }

    /**
     * Get the completion of this tier for the current month.
     */
    public function currentCompletion()
    {
        return $this->hasOne(GoalCompletion::class, 'tier_id')
            ->where('month', GoalCompletion::currentMonth());
    }

    public function isReached(): bool
    {
        return $this->currentCompletion !== null;
    }

    public function hasRewards(): bool
    {
        return ! empty($this->commands) || $this->money > 0 || $this->giftcard_balance > 0;
    }

    /**
     * Get the progress percentage of the given total towards this tier.
     */
    public function progress(float $total): float
    {
        return min(round($total / $this->amount * 100, 2), 100);
    }

    public function formatAmount(): string
    {
        return Currencies::formatAmount($this->amount, currency());
    }

    public function formatRemaining(float $total): string
    {
        return Currencies::formatAmount(max($this->amount - $total, 0), currency());
    }

    /**
     * Get the data used to display the monthly goal in the shop sidebar.
     */
    public static function sidebarData(): array
    {
        $tiers = self::with('currentCompletion')->orderBy('amount')->get();
        $total = $tiers->isEmpty() ? 0 : Payment::currentMonthTotal();

        return [
            // Progress percentage towards the last tier, kept for themes compatibility
            'goal' => $tiers->isEmpty() ? -1 : round(($total / $tiers->last()->amount) * 100, 2),
            'goalTiers' => $tiers,
            'goalTotal' => $total,
            'displayGoalAmount' => setting('shop.goals.display_amount', false),
        ];
    }

    /**
     * Complete the tiers reached by the current month total
     * that are not completed yet, and reward their participants.
     */
    public static function completeReached(): void
    {
        $total = Payment::currentMonthTotal();

        $tiers = self::where('amount', '<=', $total)
            ->whereDoesntHave('currentCompletion')
            ->orderBy('amount')
            ->get();

        foreach ($tiers as $tier) {
            $tier->complete($total);
        }
    }

    /**
     * Complete this tier for the current month, and reward the users who
     * made a payment this month. The unique completion per month ensures
     * rewards are only delivered once, even with concurrent payments.
     */
    public function complete(float $total): void
    {
        $payments = Payment::scopes(['completed', 'withRealMoney', 'currentMonth'])
            ->where('price', '>', 0)
            ->select('user_id');

        $participants = User::whereIn('id', $payments)->whereNull('deleted_at')->get();

        $completion = $this->completions()->createOrFirst([
            'month' => GoalCompletion::currentMonth(),
        ], [
            'total' => $total,
            'participants' => $participants->count(),
        ]);

        if (! $completion->wasRecentlyCreated || ! $this->hasRewards()) {
            return;
        }

        foreach ($participants->chunk(100) as $users) {
            rescue(fn () => $this->deliver($users));
        }
    }

    /**
     * Deliver the rewards of this tier to the given users. As there can be
     * many participants, the rewards stored in the database are inserted
     * in bulk, while commands and emails are sent for each user.
     *
     * @param  \Illuminate\Support\Collection<int, \Azuriom\Models\User>  $users
     */
    public function deliver(Collection $users): void
    {
        $giftcards = DB::transaction(function () use ($users) {
            if ($this->money > 0) {
                User::whereKey($users->modelKeys())->increment('money', $this->money);
            }

            $giftcards = $this->giftcard_balance > 0 ? $this->createGiftcards($users) : collect();

            $alert = new AlertNotification(trans('shop::messages.goal.notification', [
                'tier' => $this->name,
            ]));

            $this->insertAlerts($users->mapWithKeys(fn (User $user) => [$user->id => $alert]));
            $this->insertAlerts($giftcards->map(fn (Giftcard $giftcard) => $giftcard->createAlert()));

            return $giftcards;
        });

        $commands = collect($this->commands)
            ->filter(fn (mixed $command) => is_array($command))
            ->map(function (array $command) {
                $command['commands'] = array_map(fn (string $command) => str_replace([
                    '{tier_id}', '{tier_name}',
                ], [
                    $this->id, $this->name,
                ], $command), $command['commands']);

                return $command;
            });

        foreach ($users as $user) {
            rescue(fn () => $this->sendServerCommands($commands, $user));

            if (($giftcard = $giftcards->get($user->id)) !== null) {
                rescue(fn () => $user->notify(new GiftcardPurchased($giftcard)));
            }
        }
    }

    /**
     * Create a gift card for each of the given users.
     *
     * @param  \Illuminate\Support\Collection<int, \Azuriom\Models\User>  $users
     * @return \Illuminate\Support\Collection<int, \Azuriom\Plugin\Shop\Models\Giftcard> keyed by user ID
     */
    protected function createGiftcards(Collection $users): Collection
    {
        $codes = $users->mapWithKeys(fn (User $user) => [$user->id => Giftcard::randomCode()]);

        Giftcard::insert($codes->map(fn (string $code) => [
            'code' => $code,
            'balance' => $this->giftcard_balance,
            'original_balance' => $this->giftcard_balance,
            'start_at' => now(),
            'expire_at' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all());

        $giftcards = Giftcard::whereIn('code', $codes)->get()->keyBy('code');

        return $codes->map(fn (string $code) => $giftcards->get($code));
    }

    /**
     * Insert the given alert notifications in bulk.
     *
     * @param  \Illuminate\Support\Collection<int, \Azuriom\Notifications\AlertNotification>  $alerts  keyed by user ID
     */
    protected function insertAlerts(Collection $alerts): void
    {
        Notification::insert($alerts->map(fn (AlertNotification $alert, int $userId) => [
            ...$alert->toArray(),
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all());
    }
}
