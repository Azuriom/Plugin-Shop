<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $package_id
 * @property string $code
 * @property int|null $payment_item_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $assigned_at
 * @property \Azuriom\Plugin\Shop\Models\Package|null $package
 * @property \Azuriom\Plugin\Shop\Models\PaymentItem|null $paymentItem
 *
 * @method static \Illuminate\Database\Eloquent\Builder available()
 */
class PackageCode extends Model
{
    use HasTablePrefix;

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
        'code', 'payment_item_id', 'assigned_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function paymentItem()
    {
        return $this->belongsTo(PaymentItem::class);
    }

    /**
     * Assign this code to the given payment item, unless it has already been assigned.
     */
    public function assignTo(PaymentItem $item): bool
    {
        return self::whereKey($this->id)->whereNull('payment_item_id')->update([
            'payment_item_id' => $item->id,
            'assigned_at' => now(),
        ]) > 0;
    }

    /**
     * Scope a query to only include codes not yet given to a buyer.
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->whereNull('payment_item_id');
    }
}
