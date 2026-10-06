<?php

namespace App\Http\Requests\Api;

use App\Models\EnvironmentVariable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('environment'));
    }

    public function rules(): array
    {
        return [
            'key' => [
                'required', 'string', 'max:255', 'regex:'.EnvironmentVariable::KEY_PATTERN,
                Rule::unique('environment_variables', 'key')->where('environment_id', $this->route('environment')->id),
            ],
            'value' => ['nullable', 'string', 'max:10000'],
            'is_secret' => ['sometimes', 'boolean'],
        ];
    }
}
