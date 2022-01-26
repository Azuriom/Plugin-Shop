<?php

namespace Azuriom\Plugin\Shop\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'transaction_id' => ['required', 'string', 'max:100'],
            'packages' => ['required', 'array'],
            'packages.*.id' => ['required', 'exists:shop_packages,id'],
        ];
    }
}
