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
            'options' => ['nullable', 'required_if:type,dropdown,server', 'array'],
            'min' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'max' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'filter' => ['sometimes', 'nullable', 'in:alpha,alpha_num,regex'],
            'regex' => ['sometimes', 'nullable', 'required_if:filter,regex'],
            'is_required' => ['boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeCheckboxes();

        // A command cannot be dispatched to a null server
        if ($this->input('type') === 'server') {
            $this->merge(['is_required' => true]);
        }
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        if ($this->input('type') !== 'text') {
            $this->merge(['validation' => null]);

            return;
        }

        $this->merge([
            'validation' => array_filter([
                'min' => $this->integer('min'),
                'max' => $this->integer('max'),
                'filter' => $this->input('filter'),
                'regex' => $this->input('regex'),
            ]),
        ]);
    }
}
