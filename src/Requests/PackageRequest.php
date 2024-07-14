<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    use ConvertCheckbox;

    private const PERIOD_RULE = 'regex:/^\d+ (?:hours|days|weeks|months|years)$/';

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'custom_price', 'has_quantity', 'limits_no_expired', 'is_enabled',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $giftcardRules = [
            $this->filled('has_giftcard') ? 'required' : 'nullable', 'numeric',
        ];

        if ($this->filled('has_giftcard') && $this->filled('giftcard_fixed')) {
            $giftcardRules[] = 'gt:0';
        }

        return [
            'category_id' => ['required', 'exists:shop_categories,id'],
            'name' => ['required', 'string', 'max:50'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_type' => ['required', 'in:one-off,expiring,subscription'],
            'billing_period' => ['required_if:billing_type,expiring,subscription', self::PERIOD_RULE],
            'user_limit' => ['nullable', 'integer', 'gt:0'],
            'user_limit_period' => ['nullable', self::PERIOD_RULE],
            'global_limit' => ['nullable', 'integer', 'gt:0'],
            'global_limit_period' => ['nullable', self::PERIOD_RULE],
            'limits_no_expired' => ['filled', 'boolean'],
            'servers.*' => ['required', 'exists:servers,id'],
            'required_packages' => ['sometimes', 'nullable', 'array'],
            'required_roles' => ['sometimes', 'nullable', 'array'],
            'commands' => ['sometimes', 'nullable', 'array'],
            'files' => ['sometimes', 'nullable', 'array'],
            'file' => ['nullable', 'file'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'expired_role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'variables' => ['sometimes', 'nullable', 'array'],
            'money' => ['nullable', 'numeric', 'min:0'],
            'giftcard_balance' => $giftcardRules,
            'custom_price' => ['filled', 'boolean'],
            'is_enabled' => ['filled', 'boolean'],
            'has_quantity' => ['filled', 'boolean'],
            'image' => ['nullable', 'image'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeCheckboxes();

        if (! $this->filled('has_user_limit') || ! $this->filled('user_limit')) {
            $this->merge(['user_limit' => null, 'user_limit_period' => null]);
        }

        if (! $this->filled('has_global_limit') || ! $this->filled('global_limit')) {
            $this->merge(['global_limit' => null, 'global_limit_period' => null]);
        }

        if (! $this->filled('has_giftcard')) {
            $this->merge(['giftcard_balance' => null]);
        } elseif (! $this->filled('giftcard_fixed')) {
            $this->merge(['giftcard_balance' => 0]);
        }

        $this->merge([
            'commands' => $this->input('commands', []),
            'files' => $this->input('files', []),
            'required_packages' => $this->input('required_packages', []),
        ]);
    }
}
