<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'is_global', 'is_enabled',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'discount' => ['required', 'integer', 'between:0,100'],
            'roles' => ['sometimes', 'nullable', 'array'],
            'packages' => ['required_without:is_global', 'array'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'is_global' => ['filled', 'boolean'],
            'is_enabled' => ['filled', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeCheckboxes();

        if (! $this->filled('is_restricted') || ! $this->has('roles')) {
            $this->merge(['roles' => null]);
        }
    }
}
