<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityAction;
use App\Models\Project;
use App\Models\User;

class CreateProject
{
    public function __construct(private RecordActivity $record) {}

    /**
     * @param  array{name: string, description?: ?string}  $data
     */
    public function __invoke(User $user, array $data): Project
    {
        $project = $user->projects()->create($data);

        ($this->record)($project, ActivityAction::ProjectCreated, $project->name);

        return $project;
    }
}
