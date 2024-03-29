<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasImage;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Concerns\IsBuyable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property float $price
 * @property int $money
 * @property string|null $image
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Gateway[] $gateways
 */
class Offer extends Model implements Buyable
{
    use HasImage;
    use HasTablePrefix;
    use IsBuyable;
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
        'name', 'price', 'money', 'image', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'is_enabled' => 'boolean',
    ];

    /**
     * The payments gateways with which this offer can be purchased.
     */
    public function gateways()
    {
        return $this->belongsToMany(Gateway::class, 'shop_offer_gateways');
    }

    public function deliver(PaymentItem $item): void
    {
        $item->payment->user->addMoney($this->money * $item->quantity);
    }

    public function getDescription(): string
    {
        return $this->name;
    }
}
