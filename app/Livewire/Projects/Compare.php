<?php

namespace App\Livewire\Projects;

use App\Enums\VariableStatus;
use App\Livewire\Concerns\ResolvesProject;
use App\Models\Project;
use App\Services\EnvironmentComparator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Compare extends Component
{
    use ResolvesProject;

    public Project $project;

    #[Url]
    public ?string $left = null;

    #[Url]
    public ?string $right = null;

    #[Url(except: 'all')]
    public string $status = 'all';

    public function mount(string $projectSlug): void
    {
        $this->project = $this->resolveProject($projectSlug);

        $slugs = $this->project->environments()->pluck('slug');

        if (! $slugs->contains($this->left)) {
            $this->left = $slugs[0] ?? null;
        }

        if (! $slugs->contains($this->right) || $this->right === $this->left) {
            $this->right = $slugs->first(fn ($slug) => $slug !== $this->left);
        }
    }

    public function swap(): void
    {
        [$this->left, $this->right] = [$this->right, $this->left];
    }

    public function filter(string $status): void
    {
        $this->status = in_array($status, ['all', ...array_column(VariableStatus::cases(), 'value')], true) ? $status : 'all';
    }

    public function render(EnvironmentComparator $comparator): View
    {
        $environments = $this->project->environments()->with('variables')->get();
        $left = $environments->firstWhere('slug', $this->left);
        $right = $environments->firstWhere('slug', $this->right);

        $result = $left && $right && $left->isNot($right)
            ? $comparator->compare($left->variables, $right->variables)
            : null;

        $filter = VariableStatus::tryFrom($this->status);

        return view('livewire.projects.compare', [
            'environments' => $environments,
            'leftEnvironment' => $left,
            'rightEnvironment' => $right,
            'result' => $result,
            'rows' => $result ? ($filter ? $result->only($filter) : $result->rows) : [],
        ])->title("Compare · {$this->project->name}");
    }
}
