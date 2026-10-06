<?php

namespace App\Livewire\Projects;

use App\Actions\CreateEnvironment;
use App\Livewire\Concerns\ResolvesProject;
use App\Models\Environment;
use App\Models\Project;
use App\Services\EnvironmentHealth;
use App\Services\ProjectOverview;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    use ResolvesProject;

    public Project $project;

    public bool $showEnvironmentForm = false;

    public string $environmentName = '';

    public string $environmentColor = 'emerald';

    public bool $showEditForm = false;

    public string $name = '';

    public string $description = '';

    public bool $confirmingDelete = false;

    public ?string $compareLeft = null;

    public ?string $compareRight = null;

    public function mount(string $projectSlug): void
    {
        $this->project = $this->resolveProject($projectSlug);
        $this->name = $this->project->name;
        $this->description = (string) $this->project->description;

        $slugs = $this->project->environments()->pluck('slug');
        $this->compareLeft = $slugs[0] ?? null;
        $this->compareRight = $slugs[1] ?? null;
    }

    public function addEnvironment(CreateEnvironment $createEnvironment)
    {
        $this->authorize('update', $this->project);

        $this->validate([
            'environmentName' => [
                'required', 'string', 'max:50',
                Rule::unique('environments', 'name')->where('project_id', $this->project->id),
                // Names that differ only by punctuation would collide on the slug.
                fn ($attribute, $value, $fail) => $this->project->environments()->where('slug', Str::slug($value))->exists()
                    ? $fail('An environment with a very similar name already exists.') : null,
            ],
            'environmentColor' => ['required', Rule::in(Environment::COLORS)],
        ], attributes: ['environmentName' => 'name']);

        $environment = $createEnvironment($this->project, ['name' => $this->environmentName, 'color' => $this->environmentColor]);

        return $this->redirectRoute('projects.environments.show', [$this->project->slug, $environment->slug], navigate: true);
    }

    public function updateProject(): void
    {
        $this->authorize('update', $this->project);

        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $this->project->update(['name' => $validated['name'], 'description' => $validated['description'] ?: null]);

        $this->showEditForm = false;
        $this->dispatch('toast', message: 'Project updated.');
    }

    public function deleteProject()
    {
        $this->authorize('delete', $this->project);

        $this->project->delete();

        return $this->redirectRoute('dashboard', navigate: true);
    }

    public function compare()
    {
        $this->validate([
            'compareLeft' => 'required|different:compareRight',
            'compareRight' => 'required',
        ], ['compareLeft.different' => 'Pick two different environments to compare.']);

        return $this->redirectRoute('projects.compare', ['projectSlug' => $this->project->slug, 'left' => $this->compareLeft, 'right' => $this->compareRight], navigate: true);
    }

    public function render(EnvironmentHealth $health, ProjectOverview $overview): View
    {
        $environments = $this->project->environments()->with('variables')->get();
        $expected = $health->expectedKeys($environments);

        return view('livewire.projects.show', [
            'environments' => $environments,
            'reports' => $environments->mapWithKeys(fn ($environment) => [$environment->id => $health->check($environment, $expected)]),
            'summary' => $overview->summarize($environments),
            'activities' => $this->project->activities()->with('user')->limit(8)->get(),
        ])->title($this->project->name);
    }
}
