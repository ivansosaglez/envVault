<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityAction;
use App\Models\Environment;
use App\Models\Project;

class CreateEnvironment
{
    public function __construct(private RecordActivity $record) {}

    /**
     * @param  array{name: string, color?: ?string}  $data
     */
    public function __invoke(Project $project, array $data): Environment
    {
        $environment = $project->environments()->create($data);

        ($this->record)($project, ActivityAction::EnvironmentCreated, $environment->name);

        return $environment;
    }
}
