<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\HasUser;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property float $price
 * @property string $currency
 * @property string $type
 * @property string $status
 * @property string $payment_type
 * @property string $payment_id
 * @property array $items
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Azuriom\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder success()
 * @method static \Illuminate\Database\Eloquent\Builder completed()
 * @method static \Illuminate\Database\Eloquent\Builder pending()
 */
class Payment extends Model
{
    use HasTablePrefix;
    use HasUser;

    protected const STATUS_LIST = [
        'CREATED', 'CANCELLED', 'PENDING', 'EXPIRED', 'SUCCESS', 'DELIVERED', 'ERROR',
    ];

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
        'price', 'currency', 'status', 'payment_type', 'payment_id', 'type', 'items',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'items' => 'array',
    ];

    /**
     * Get the category of this package.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function status()
    {
        return self::STATUS_LIST;
    }

    public function getTypeName()
    {
        $paymentManager = app(PaymentManager::class);

        if (! $paymentManager->hasPaymentMethod($this->payment_type)) {
            return $this->payment_type;
        }

        return $paymentManager->getPaymentMethod($this->payment_type)->name();
    }

    public function isPending()
    {
        return $this->status === 'CREATED' || $this->status === 'PENDING';
    }

    public function isCompleted()
    {
        return $this->status === 'SUCCESS' || $this->status === 'DELIVERED';
    }

    public function scopeSuccess(Builder $query)
    {
        return $query->where('status', 'SUCCESS');
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->whereIn('status', ['SUCCESS', 'DELIVERED']);
    }

    public function scopePending(Builder $query)
    {
        return $query->whereIn('status', ['CREATED', 'PENDING']);
    }
}
