<?php

namespace App\Livewire\Projects;

use App\Actions\CreateProject;
use App\Models\Project;
use App\Services\EnvironmentHealth;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Projects')]
class Index extends Component
{
    public bool $showForm = false;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public string $description = '';

    public function create(CreateProject $createProject)
    {
        $this->validate();

        $project = $createProject(auth()->user(), [
            'name' => $this->name,
            'description' => $this->description ?: null,
        ]);

        return $this->redirectRoute('projects.show', $project->slug, navigate: true);
    }

    public function render(EnvironmentHealth $health): View
    {
        $projects = auth()->user()->projects()
            ->with('environments.variables')
            ->orderBy('name')
            ->get()
            ->each(function (Project $project) use ($health) {
                $expected = $health->expectedKeys($project->environments);

                $project->setRelation('healthReports', $project->environments->mapWithKeys(
                    fn ($environment) => [$environment->id => $health->check($environment, $expected)]
                ));
            });

        return view('livewire.projects.index', ['projects' => $projects]);
    }
}
