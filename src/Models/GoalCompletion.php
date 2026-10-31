<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Plugin\Shop\Payment\Currencies;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $tier_id
 * @property string $month
 * @property float $total
 * @property int $participants
 * @property \Carbon\Carbon $created_at
 * @property \Azuriom\Plugin\Shop\Models\GoalTier|null $tier
 */
class GoalCompletion extends Model
{
    use HasTablePrefix;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

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
        'month', 'total', 'participants',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total' => 'float',
        'participants' => 'integer',
    ];

    /**
     * Get the tier of this completion.
     */
    public function tier()
    {
        return $this->belongsTo(GoalTier::class, 'tier_id');
    }

    public function formatTotal(): string
    {
        return Currencies::formatAmount($this->total, currency());
    }

    /**
     * Get the key of the current month, as stored in the month column.
     */
    public static function currentMonth(): string
    {
        return now()->format('Y-m');
    }
}
