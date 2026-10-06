<?php

namespace App\Http\Resources;

use App\Models\Environment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Environment */
class EnvironmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'variables_count' => $this->whenCounted('variables'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
