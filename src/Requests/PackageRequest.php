<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'custom_price', 'need_online', 'has_quantity', 'is_enabled',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:shop_categories,id'],
            'name' => ['required', 'string', 'max:50'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'user_limit' => ['nullable', 'integer', 'min:0'],
            'global_limit' => ['nullable', 'integer', 'min:0', 'gte:user_limit'],
            'servers.*' => ['required', 'exists:servers,id'],
            'required_packages' => ['sometimes', 'nullable', 'array'],
            'required_roles' => ['sometimes', 'nullable', 'array'],
            'commands' => ['sometimes', 'nullable', 'array'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'money' => ['nullable', 'numeric', 'min:0'],
            'giftcard_balance' => ['nullable', 'numeric', 'min:0'],
            'custom_price' => ['filled', 'boolean'],
            'need_online' => ['filled', 'boolean'],
            'is_enabled' => ['filled', 'boolean'],
            'has_quantity' => ['filled', 'boolean'],
            'image' => ['nullable', 'image'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeCheckboxes();

        $this->merge([
            'commands' => $this->input('commands', []),
            'required_packages' => $this->input('required_packages', []),
        ]);
    }
}
