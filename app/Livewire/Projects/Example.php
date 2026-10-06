<?php

namespace App\Livewire\Projects;

use App\Livewire\Concerns\ResolvesProject;
use App\Models\Project;
use App\Services\EnvExampleGenerator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class Example extends Component
{
    use ResolvesProject;

    public Project $project;

    /** Slug of a single environment, or empty for the keys of every environment. */
    #[Url(except: '')]
    public string $environment = '';

    public function mount(string $projectSlug): void
    {
        $this->project = $this->resolveProject($projectSlug);

        if ($this->environment !== '' && ! $this->project->environments()->where('slug', $this->environment)->exists()) {
            $this->environment = '';
        }
    }

    public function download(EnvExampleGenerator $generator): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print ($this->output($generator)),
            '.env.example',
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    private function output(EnvExampleGenerator $generator): string
    {
        $query = $this->project->variables()->select('environment_variables.key');

        if ($this->environment !== '') {
            $query->where('environments.slug', $this->environment);
        }

        return $generator->generate($query->distinct()->pluck('key'));
    }

    public function render(EnvExampleGenerator $generator): View
    {
        return view('livewire.projects.example', [
            'environments' => $this->project->environments()->get(),
            'output' => $this->output($generator),
        ])->title(".env.example · {$this->project->name}");
    }
}
