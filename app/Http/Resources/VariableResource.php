<?php

namespace App\Http\Resources;

use App\Models\EnvironmentVariable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The API never returns the value of a secret: `value` is null and `has_value`
 * says whether one is stored. Values of regular variables are returned as-is.
 *
 * @mixin EnvironmentVariable
 */
class VariableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'environment_id' => $this->environment_id,
            'key' => $this->key,
            'value' => $this->is_secret ? null : (string) $this->value,
            'is_secret' => $this->is_secret,
            'has_value' => $this->hasValue(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
