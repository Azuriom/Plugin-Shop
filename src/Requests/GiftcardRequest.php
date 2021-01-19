<?php

namespace Azuriom\Plugin\Shop\Requests;

use Illuminate\Validation\Rule;
use Azuriom\Plugin\Shop\Models\Giftcard;
use Illuminate\Foundation\Http\FormRequest;
use Azuriom\Http\Requests\Traits\ConvertCheckbox;

class GiftcardRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The checkboxes attributes.
     *
     * @var array
     */
    protected $checkboxes = [
        'is_enabled',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique(Giftcard::class)->ignore($this->giftcard, 'code')],
            'amount' => ['required', 'integer', 'min:0'],
            'global_limit' => ['nullable', 'integer', 'min:1'],
            'start_at' => ['required', 'date'],
            'expire_at' => ['required', 'date', 'after:start_at'],
            'is_enabled' => ['filled', 'boolean'],
        ];
    }
}
