<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Concerns\IsBuyable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property float $price
 * @property int $money
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Gateway[] $gateways
 */
class Offer extends Model implements Buyable
{
    use IsBuyable;
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
        'name', 'price', 'money', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
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

    public function deliver(User $user, int $quantity = 1)
    {
        $user->addMoney($this->money * $quantity);
        $user->save();
    }
}
