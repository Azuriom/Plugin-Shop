<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $name
 * @property float $price
 * @property int $quantity
 * @property string $buyable_type
 * @property int $buyable_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Azuriom\Plugin\Shop\Models\Payment $payment
 * @property \Azuriom\Plugin\Shop\Models\Package|\Azuriom\Plugin\Shop\Models\Offer|null $buyable
 */
class PaymentItem extends Model
{
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
        'name', 'price', 'quantity',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
    ];

    /**
     * Get the payment associated to this payment item.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the purchased model.
     */
    public function buyable()
    {
        return $this->morphTo('buyable');
    }

    public function deliver()
    {
        if ($this->buyable !== null) {
            $this->buyable->deliver($this->payment->user, $this->quantity);
        }
    }
}
