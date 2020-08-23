<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $fees
 * @property array $data
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Offer[] $offers
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Gateway extends Model
{
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
        'name', 'type', 'fees', 'data', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the offers that can be used with this gateway.
     */
    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'shop_offer_gateways');
    }

    /**
     * Get the associated payment method.
     *
     * @return \Azuriom\Plugin\Shop\Payment\PaymentMethod
     */
    public function paymentMethod()
    {
        return payment_manager()->getPaymentMethodOrFail($this->type, $this);
    }

    /**
     * Scope a query to only include enabled payment gateways.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', true);
    }
}
