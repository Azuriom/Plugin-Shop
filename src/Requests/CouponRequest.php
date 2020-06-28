<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The checkboxes attributes.
     *
     * @var array
     */
    protected $checkboxes = [
        'is_enabled', 'is_global',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:100'],
            'discount' => ['required', 'integer', 'between:0,100'],
            'packages' => ['required_without:is_global', 'array'],
            'start_at' => ['nullable', 'date'],
            'expire_at' => ['nullable', 'date', 'after:start_at'],
            'is_enabled' => ['filled', 'boolean'],
            'is_global' => ['filled', 'boolean'],
        ];
    }
}
