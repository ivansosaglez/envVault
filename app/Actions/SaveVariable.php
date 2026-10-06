<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityAction;
use App\Models\Environment;
use App\Models\EnvironmentVariable;

class SaveVariable
{
    public function __construct(private RecordActivity $record) {}

    /**
     * Creates the variable, or updates it when one is given.
     *
     * On update, `value` is optional: leaving it out keeps the stored value, so
     * a secret never has to travel back to the client just to edit its key.
     *
     * @param  array{key: string, value?: ?string, is_secret?: bool}  $data
     */
    public function __invoke(Environment $environment, array $data, ?EnvironmentVariable $variable = null): EnvironmentVariable
    {
        if (array_key_exists('value', $data)) {
            $data['value'] = (string) $data['value'];
        }

        if ($variable === null) {
            $data += ['value' => '', 'is_secret' => EnvironmentVariable::looksSecret($data['key'])];

            $variable = $environment->variables()->create($data);
            $action = ActivityAction::VariableAdded;
        } else {
            $variable->update($data);
            $action = ActivityAction::VariableUpdated;
        }

        ($this->record)($environment->project, $action, $variable->key);

        return $variable;
    }
}
