<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class TierRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'is_enabled',
    ];

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeCheckboxes();

        $type = $this->input('type');
        $rewardData = $this->input('reward_data', []);

        if ($type === 'commands') {
            $this->merge([
                'reward_data' => null,
            ]);
        } elseif ($type === 'coupon') {
            $this->merge([
                'commands' => null,
                'reward_data' => [
                    'discount' => Arr::get($rewardData, 'discount'),
                    'is_fixed' => Arr::get($rewardData, 'is_fixed', false),
                    'duration_days' => Arr::get($rewardData, 'duration_days'),
                ],
            ]);
        } elseif ($type === 'giftcard') {
            $this->merge([
                'commands' => null,
                'reward_data' => [
                    'balance' => Arr::get($rewardData, 'balance'),
                ],
            ]);
        } elseif ($type === 'money') {
            $this->merge([
                'commands' => null,
                'reward_data' => [
                    'amount' => Arr::get($rewardData, 'amount'),
                ],
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'min_spend' => ['required', 'numeric', 'min:0'],
            'icon' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:commands,coupon,giftcard,money'],
            'is_enabled' => ['filled', 'boolean'],
            'commands' => ['sometimes', 'nullable', 'array'],
            'reward_data' => ['sometimes', 'nullable', 'array'],
            'reward_data.discount' => ['required_if:type,coupon', 'nullable', 'numeric', 'min:0'],
            'reward_data.is_fixed' => ['sometimes', 'nullable', 'boolean'],
            'reward_data.duration_days' => ['nullable', 'integer', 'min:1'],
            'reward_data.balance' => ['required_if:type,giftcard', 'nullable', 'numeric', 'min:0.01'],
            'reward_data.amount' => ['required_if:type,money', 'nullable', 'numeric', 'min:0.01'],
        ];
    }
}
