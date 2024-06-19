<?php

namespace Azuriom\Plugin\Shop\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Azuriom\Plugin\Shop\Models\Variable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VariableRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'is_required',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'alpha_dash:ascii', 'lowercase', 'max:50',
                Rule::unique(Variable::class)->ignore($this->variable, 'name'),
            ],
            'description' => ['required', 'string', 'max:200'],
            'type' => ['required', 'string', Rule::in(Variable::TYPES)],
            'options' => ['nullable', 'required_if:type,dropdown', 'array'],
            'is_required' => ['boolean'],
        ];
    }
}
