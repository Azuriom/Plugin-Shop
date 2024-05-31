<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $gateway_id
 * @property int $model_id
 * @property string $model_type
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Model $model
 * @property \Azuriom\Plugin\Shop\Models\Gateway $gateway
 */
class GatewayMetadata extends Model
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
        'key', 'value',
    ];

    public function model()
    {
        return $this->morphTo('model');
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }
}
