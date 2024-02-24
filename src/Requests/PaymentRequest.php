<?php

namespace Azuriom\Plugin\Shop\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'transaction_id' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'packages' => ['required', 'array'],
        ];
    }
}
