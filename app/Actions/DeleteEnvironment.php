<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityAction;
use App\Models\Environment;

class DeleteEnvironment
{
    public function __construct(private RecordActivity $record) {}

    public function __invoke(Environment $environment): void
    {
        $environment->delete();

        ($this->record)($environment->project, ActivityAction::EnvironmentDeleted, $environment->name);
    }
}
