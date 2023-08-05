<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Plugin\Shop\Models\Giftcard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GiftcardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique(Giftcard::class)->ignore($this->giftcard, 'code')],
            'balance' => ['required', 'numeric', 'min:0'],
            'start_at' => ['required', 'date'],
            'expire_at' => ['required', 'date', 'after:start_at'],
        ];
    }
}
