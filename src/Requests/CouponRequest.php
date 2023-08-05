<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Azuriom\Plugin\Shop\Models\Coupon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'can_cumulate', 'is_enabled', 'is_global',
    ];

    /**
     * Get the validator instance for the request.
     */
    protected function getValidatorInstance(): Validator
    {
        return parent::getValidatorInstance()->sometimes('discount', 'max:100', function ($input) {
            return ! $input->is_fixed;
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique(Coupon::class)->ignore($this->coupon, 'code')],
            'discount' => ['required', 'integer', 'min:0'],
            'packages' => ['required_without:is_global', 'array'],
            'user_limit' => ['nullable', 'integer', 'min:0'],
            'global_limit' => ['nullable', 'integer', 'min:0'],
            'start_at' => ['required', 'date'],
            'expire_at' => ['required', 'date', 'after:start_at'],
            'can_cumulate' => ['filled', 'boolean'],
            'is_enabled' => ['filled', 'boolean'],
            'is_global' => ['filled', 'boolean'],
            'is_fixed' => ['filled', 'boolean'],
        ];
    }
}
