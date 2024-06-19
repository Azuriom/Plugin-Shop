<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property bool $is_required
 * @property ?array $options
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 */
class Variable extends Model
{
    use HasTablePrefix;

    public const TYPES = ['text', 'number', 'email', 'checkbox', 'dropdown'];

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
        'name', 'description', 'type', 'options', 'is_required',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
    ];

    /**
     * Get the packages on which this coupon code is effective.
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'shop_package_variable');
    }

    public function getValidationRule(): array
    {
        $rules = match ($this->type) {
            'text' => ['string', 'max:100'],
            'number' => ['numeric'],
            'email' => ['email', 'max:100'],
            'dropdown' => ['string', Rule::in(Arr::pluck($this->options ?? [], 'value'))],
            default => [],
        };

        return array_merge([
            $this->is_required ? 'required' : 'nullable',
        ], $rules);
    }
}
