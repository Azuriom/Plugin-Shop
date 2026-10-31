<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Server;
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
 * @property ?array $validation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 */
class Variable extends Model
{
    use HasTablePrefix;

    public const TYPES = ['text', 'number', 'email', 'checkbox', 'dropdown', 'server'];

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
        'name', 'description', 'type', 'options', 'validation', 'is_required',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'validation' => 'array',
    ];

    /**
     * Get the packages that use this variable.
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'shop_package_variable');
    }

    public function getValidationRule(): array
    {
        $validation = $this->validation ?? [];
        $min = $validation['min'] ?? 0;
        $max = $validation['max'] ?? 100;
        $filter = $validation['filter'] ?? null;

        $rules = match ($this->type) {
            'text' => ['string', 'min:'.$min, 'max:'.$max],
            'number' => ['numeric'],
            'email' => ['email', 'max:100'],
            'dropdown' => ['string', Rule::in(Arr::pluck($this->options ?? [], 'value'))],
            'server' => [Rule::in($this->options ?? [])],
            default => [],
        };

        if ($filter !== null && $this->type === 'text') {
            $rules[] = $filter === 'regex' ? 'regex:'.$validation['regex'] : $filter;
        }

        return array_merge([
            $this->is_required ? 'required' : 'nullable',
        ], $rules);
    }

    public function dropdownOptions(): array
    {
        if ($this->type === 'server') {
            return Server::findMany($this->options ?? [])
                ->map(fn (Server $server) => [
                    'name' => $server->name,
                    'value' => $server->id,
                ])
                ->all();
        }

        return $this->options ?? [];
    }

    /**
     * Get the dropdown option with the given value, if any.
     */
    public function selectedOption(?string $selectedValue): ?array
    {
        if ($this->type !== 'dropdown' || $selectedValue === null) {
            return null;
        }

        return Arr::first($this->options ?? [], fn (array $option) => $option['value'] === $selectedValue);
    }

    /**
     * Get the price change of a dropdown option.
     */
    public function priceChange(array $option, float $price): float
    {
        $change = (float) ($option['price_change'] ?? 0);

        return round(($option['is_fixed'] ?? false) ? $change : $price * $change / 100, 2);
    }

    /**
     * Get the price change of a dropdown option to display it.
     */
    public function formatPriceChange(array $option): ?string
    {
        $change = (float) ($option['price_change'] ?? 0);

        if ($change === 0.0) {
            return null;
        }

        $value = ($option['is_fixed'] ?? false) ? shop_format_amount(abs($change)) : abs($change).'%';

        return ($change > 0 ? '+' : '-').$value;
    }
}
