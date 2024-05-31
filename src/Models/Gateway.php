<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $fees
 * @property array $data
 * @property int $position
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Offer[] $offers
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\GatewayMetadata[] $metadata
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Gateway extends Model
{
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
        'name', 'type', 'fees', 'data', 'position', 'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
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
     * Get the metadata associated with this gateway.
     */
    public function metadata()
    {
        return $this->hasMany(GatewayMetadata::class);
    }

    /**
     * Get the associated payment method.
     */
    public function paymentMethod(): PaymentMethod
    {
        return payment_manager()->getPaymentMethodOrFail($this->type, $this);
    }

    public function isSupported(): bool
    {
        return payment_manager()->hasPaymentMethod($this->type);
    }

    public function getTypeName(): string
    {
        return self::getNameByType($this->type);
    }

    public static function getNameByType(string $gatewayType): string
    {
        if ($gatewayType === 'free') {
            return trans('shop::messages.free');
        }

        if ($gatewayType === 'manual') {
            return trans('shop::messages.payment.manual');
        }

        $method = payment_manager()->getPaymentMethod($gatewayType);

        return $method !== null ? $method->name() : $gatewayType;
    }

    /**
     * Scope a query to only include enabled payment gateways.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true)->orderBy('position');
    }
}
