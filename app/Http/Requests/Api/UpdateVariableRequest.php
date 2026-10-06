<?php

namespace App\Http\Requests\Api;

use App\Models\EnvironmentVariable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('variable'));
    }

    public function rules(): array
    {
        $variable = $this->route('variable');

        return [
            'key' => [
                'sometimes', 'required', 'string', 'max:255', 'regex:'.EnvironmentVariable::KEY_PATTERN,
                Rule::unique('environment_variables', 'key')
                    ->where('environment_id', $variable->environment_id)
                    ->ignore($variable->id),
            ],
            'value' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'is_secret' => ['sometimes', 'boolean'],
        ];
    }
}
