<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Azuriom\Plugin\Shop\Models\Offer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:50', Rule::unique(Offer::class)->ignore($this->offer, 'name')],
            'price' => ['required', 'numeric', 'min:0'],
            'money' => ['required', 'integer', 'min:0'],
            'gateways' => ['required', 'array'],
            'is_enabled' => ['filled', 'boolean'],
        ];
    }
}
