<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The checkboxes attributes.
     *
     * @var array
     */
    protected $checkboxes = [
        'need_online', 'has_quantity', 'is_enabled',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'translations.*.locale' => ['required', 'string', 'max:50'],
            'translations.*.name' => ['required', 'string', 'max:50'],
            'translations.*.short_description' => ['required', 'string', 'max:255'],
            'translations.*.description' => ['required', 'string'],
            'category_id' => ['required', 'exists:shop_categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'user_limit' => ['nullable', 'integer', 'min:0'],
            'required_packages' => ['sometimes', 'nullable', 'array'],
            'commands' => ['sometimes', 'nullable', 'array'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'need_online' => ['filled', 'boolean'],
            'is_enabled' => ['filled', 'boolean'],
            'has_quantity' => ['filled', 'boolean'],
            'image' => ['nullable', 'image'],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated()
    {
        return array_merge(parent::validated(), [
            'commands' => array_filter($this->input('commands', [])),
            'required_packages' => $this->input('required_packages', []),
        ]);
    }
}
