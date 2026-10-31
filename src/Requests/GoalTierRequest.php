<?php

namespace Azuriom\Plugin\Shop\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalTierRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'commands' => ['sometimes', 'nullable', 'array'],
            'money' => ['nullable', 'numeric', 'min:0'],
            'giftcard_balance' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing(['commands' => []]);
    }
}
