<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Azuriom\Rules\Slug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'cumulate_purchases', 'is_enabled',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $current = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:50'],
            'slug' => [
                'required', 'max:100', new Slug(), Rule::unique('shop_categories')->ignore($current, 'slug'),
            ],
            'parent_id' => ['nullable', 'exists:shop_categories,id'],
            'description' => ['nullable', 'string'],
            'cumulate_purchases' => ['filled', 'boolean'],
            'is_enabled' => ['filled', 'boolean'],
        ];
    }
}
