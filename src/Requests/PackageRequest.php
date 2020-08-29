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
            'category_id' => ['required', 'exists:shop_categories,id'],
            'name' => ['required', 'string', 'max:50'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'user_limit' => ['nullable', 'integer', 'min:0'],
            'required_packages' => ['sometimes', 'nullable', 'array'],
            'commands' => ['sometimes', 'nullable', 'array'],
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
        $commands = array_filter($this->input('commands', []));

        return ['commands' => $commands] + $this->validator->validated();
    }
}
