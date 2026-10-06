<?php

namespace App\Enums;

enum ActivityAction: string
{
    case ProjectCreated = 'project_created';
    case EnvironmentCreated = 'environment_created';
    case EnvironmentDeleted = 'environment_deleted';
    case VariableAdded = 'variable_added';
    case VariableUpdated = 'variable_updated';
    case VariableDeleted = 'variable_deleted';
    case VariablesImported = 'variables_imported';

    /**
     * Human readable sentence, e.g. `added DB_HOST`. Never contains a value.
     */
    public function describe(?string $subject): string
    {
        return match ($this) {
            self::ProjectCreated => "created the project \"{$subject}\"",
            self::EnvironmentCreated => "created \"{$subject}\"",
            self::EnvironmentDeleted => "deleted \"{$subject}\"",
            self::VariableAdded => "added {$subject}",
            self::VariableUpdated => "updated {$subject}",
            self::VariableDeleted => "deleted {$subject}",
            self::VariablesImported => "imported variables into \"{$subject}\"",
        };
    }
}
