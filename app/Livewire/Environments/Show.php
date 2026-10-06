<?php

namespace App\Livewire\Environments;

use App\Actions\DeleteEnvironment;
use App\Actions\DeleteVariable;
use App\Actions\ImportVariables;
use App\Actions\SaveVariable;
use App\Livewire\Concerns\ResolvesProject;
use App\Models\Environment;
use App\Models\EnvironmentVariable;
use App\Models\Project;
use App\Services\EnvironmentHealth;
use App\Services\EnvParser;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Show extends Component
{
    use ResolvesProject, WithPagination;

    public Project $project;

    public Environment $environment;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'asc')]
    public string $sort = 'asc';

    // Create / edit form
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $key = '';

    public string $value = '';

    public bool $isSecret = false;

    public bool $valueTouched = false;

    // Reveal state holds ids only; values are rendered server-side on demand.
    /** @var list<int> */
    public array $revealed = [];

    public bool $confirmingDelete = false;

    public ?int $confirmingDeleteId = null;

    public bool $confirmingEnvironmentDelete = false;

    // Import
    public bool $showImport = false;

    public string $importContent = '';

    public bool $importAnalyzed = false;

    /** @var list<string> */
    public array $replace = [];

    public function mount(string $projectSlug, string $environmentSlug): void
    {
        $this->project = $this->resolveProject($projectSlug);
        $this->environment = $this->project->environments()->where('slug', $environmentSlug)->firstOrFail();
        $this->authorize('view', $this->environment);

        if (! in_array($this->sort, ['asc', 'desc'], true)) {
            $this->sort = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedValue(): void
    {
        $this->valueTouched = true;
    }

    public function toggleSort(): void
    {
        $this->sort = $this->sort === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    // ----- Variables -----

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'key', 'value', 'isSecret', 'valueTouched');
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $variable = $this->findVariable($id);
        $this->authorize('update', $variable);

        $this->resetValidation();
        $this->editingId = $variable->id;
        $this->key = $variable->key;
        $this->isSecret = $variable->is_secret;
        $this->valueTouched = false;
        // A secret's value never goes back to the browser just to edit it.
        $this->value = $variable->is_secret ? '' : (string) $variable->value;
        $this->showForm = true;
    }

    public function saveVariable(SaveVariable $saveVariable): void
    {
        $variable = $this->editingId ? $this->findVariable($this->editingId) : null;
        $this->authorize('update', $variable ?? $this->environment);

        $this->validate([
            'key' => [
                'required', 'string', 'max:255', 'regex:'.EnvironmentVariable::KEY_PATTERN,
                Rule::unique('environment_variables', 'key')
                    ->where('environment_id', $this->environment->id)
                    ->ignore($variable?->id),
            ],
            'value' => 'nullable|string|max:10000',
            'isSecret' => 'boolean',
        ], [
            'key.regex' => 'Use letters, numbers, underscores and dots, and do not start with a number.',
            'key.unique' => 'This variable already exists in the environment.',
        ], ['key' => 'name']);

        $data = ['key' => $this->key, 'is_secret' => $this->isSecret];

        if ($variable === null || ! $variable->is_secret || $this->valueTouched) {
            $data['value'] = $this->value;
        }

        $saveVariable($this->environment, $data, $variable);

        $this->showForm = false;
        $this->dispatch('toast', message: $variable ? "{$this->key} updated." : "{$this->key} added.");
    }

    public function reveal(int $id): void
    {
        $variable = $this->findVariable($id);
        $this->authorize('reveal', $variable);

        $this->revealed[] = $variable->id;
    }

    public function hide(int $id): void
    {
        $this->revealed = array_values(array_diff($this->revealed, [$id]));
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $this->findVariable($id)->id;
        $this->confirmingDelete = true;
    }

    public function deleteVariable(DeleteVariable $deleteVariable): void
    {
        $variable = $this->findVariable((int) $this->confirmingDeleteId);
        $this->authorize('delete', $variable);

        $deleteVariable($variable);

        $this->hide($variable->id);
        $this->confirmingDeleteId = null;
        $this->confirmingDelete = false;
        $this->dispatch('toast', message: "{$variable->key} deleted.");
    }

    public function deleteEnvironment(DeleteEnvironment $deleteEnvironment)
    {
        $this->authorize('delete', $this->environment);

        $deleteEnvironment($this->environment);

        return $this->redirectRoute('projects.show', $this->project->slug, navigate: true);
    }

    // ----- Import -----

    public function openImport(): void
    {
        $this->reset('importContent', 'importAnalyzed', 'replace');
        $this->showImport = true;
    }

    public function analyzeImport(): void
    {
        $this->validate(['importContent' => 'required|string|max:200000']);

        $this->replace = [];
        $this->importAnalyzed = true;
    }

    public function editImport(): void
    {
        $this->importAnalyzed = false;
    }

    public function confirmImport(ImportVariables $import, EnvParser $parser): void
    {
        $this->authorize('update', $this->environment);

        $result = $import->import($this->environment, $parser->parse($this->importContent), $this->replace);

        $this->showImport = false;
        $this->reset('importContent', 'importAnalyzed', 'replace');
        $this->dispatch('toast', message: "Imported {$result['created']} new, replaced {$result['replaced']}, skipped {$result['skipped']}.");
    }

    private function findVariable(int $id): EnvironmentVariable
    {
        return $this->environment->variables()->whereKey($id)->firstOrFail();
    }

    public function render(EnvironmentHealth $health, EnvParser $parser, ImportVariables $import): View
    {
        $variables = $this->environment->variables()
            ->search($this->search)
            ->reorder('key', $this->sort)
            ->paginate(25);

        $environments = $this->project->environments()->with('variables')->get();
        $report = $health->check(
            $environments->firstWhere('id', $this->environment->id),
            $health->expectedKeys($environments),
        );

        $plan = $this->showImport && $this->importAnalyzed
            ? $import->plan($this->environment, $parser->parse($this->importContent))
            : null;

        $revealedValues = $this->environment->variables()
            ->whereKey($this->revealed)
            ->get()
            ->pluck('value', 'id');

        return view('livewire.environments.show', [
            'variables' => $variables,
            'report' => $report,
            'totalVariables' => $this->environment->variables()->count(),
            'plan' => $plan,
            'revealedValues' => $revealedValues,
            'pendingDelete' => $this->confirmingDeleteId ? $this->environment->variables()->find($this->confirmingDeleteId) : null,
            'otherEnvironments' => $environments->where('id', '!=', $this->environment->id),
        ])->title("{$this->environment->name} · {$this->project->name}");
    }
}
