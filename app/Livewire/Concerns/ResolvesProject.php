<?php

namespace App\Livewire\Concerns;

use App\Models\Project;

trait ResolvesProject
{
    /**
     * Projects are looked up inside the signed-in user's own projects (slugs are
     * only unique per user), then authorized through the policy as a second line of defense.
     */
    protected function resolveProject(string $slug): Project
    {
        $project = auth()->user()->projects()->where('slug', $slug)->firstOrFail();

        $this->authorize('view', $project);

        return $project;
    }
}
