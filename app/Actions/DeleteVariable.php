<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityAction;
use App\Models\EnvironmentVariable;

class DeleteVariable
{
    public function __construct(private RecordActivity $record) {}

    public function __invoke(EnvironmentVariable $variable): void
    {
        $variable->delete();

        ($this->record)($variable->environment->project, ActivityAction::VariableDeleted, $variable->key);
    }
}
