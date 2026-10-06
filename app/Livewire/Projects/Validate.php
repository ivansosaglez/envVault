<?php

namespace App\Livewire\Projects;

use App\Livewire\Concerns\ResolvesProject;
use App\Models\Project;
use App\Services\EnvValidator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Validate extends Component
{
    use ResolvesProject;

    public Project $project;

    #[Url]
    public ?string $environment = null;

    public string $content = '';

    public bool $validated = false;

    public function mount(string $projectSlug): void
    {
        $this->project = $this->resolveProject($projectSlug);

        if (! $this->project->environments()->where('slug', $this->environment)->exists()) {
            $this->environment = $this->project->environments()->value('slug');
        }
    }

    public function validateEnv(): void
    {
        $this->validate([
            'content' => 'required|string|max:200000',
            'environment' => 'required',
        ], ['content.required' => 'Paste the contents of a .env file to validate.']);

        $this->validated = true;
    }

    public function updatedEnvironment(): void
    {
        $this->validated = false;
    }

    public function clear(): void
    {
        $this->reset('content', 'validated');
    }

    public function render(EnvValidator $validator): View
    {
        $environments = $this->project->environments()->get();
        $target = $environments->firstWhere('slug', $this->environment)?->load('variables');

        return view('livewire.projects.validate', [
            'environments' => $environments,
            'target' => $target,
            'report' => $this->validated && $target ? $validator->validate($this->content, $target) : null,
        ])->title("Validate .env · {$this->project->name}");
    }
}
