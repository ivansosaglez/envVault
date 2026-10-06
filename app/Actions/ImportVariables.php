<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ImportPlan;
use App\Data\ParsedEnv;
use App\Enums\ActivityAction;
use App\Models\Environment;
use App\Models\EnvironmentVariable;

/**
 * Two steps so the user always sees what is about to happen:
 *  - plan():   what is new and what already exists (nothing is written)
 *  - import(): writes new keys; existing keys are only touched when listed in $replace
 */
class ImportVariables
{
    public function __construct(private RecordActivity $record) {}

    public function plan(Environment $environment, ParsedEnv $parsed): ImportPlan
    {
        $existing = $environment->variables()->get(['id', 'environment_id', 'key', 'is_secret'])->keyBy('key');

        $keys = array_keys($parsed->variables);
        $conflicts = array_values(array_filter($keys, fn (string $key) => $existing->has($key)));

        return new ImportPlan(
            new: array_values(array_diff($keys, $conflicts)),
            conflicts: $conflicts,
            secretConflicts: array_values(array_filter($conflicts, fn (string $key) => $existing[$key]->is_secret)),
            parsed: $parsed,
        );
    }

    /**
     * @param  list<string>  $replace  Existing keys the user explicitly chose to overwrite.
     * @return array{created: int, replaced: int, skipped: int}
     */
    public function import(Environment $environment, ParsedEnv $parsed, array $replace = []): array
    {
        $result = ['created' => 0, 'replaced' => 0, 'skipped' => 0];
        $existing = $environment->variables()->get()->keyBy('key');

        foreach ($parsed->variables as $key => $value) {
            $variable = $existing->get($key);

            if ($variable === null) {
                $environment->variables()->create([
                    'key' => $key,
                    'value' => $value,
                    'is_secret' => EnvironmentVariable::looksSecret($key),
                ]);
                $result['created']++;
            } elseif (in_array($key, $replace, true)) {
                $variable->update(['value' => $value, 'is_secret' => $variable->is_secret || EnvironmentVariable::looksSecret($key)]);
                $result['replaced']++;
            } else {
                $result['skipped']++;
            }
        }

        if ($result['created'] + $result['replaced'] > 0) {
            ($this->record)($environment->project, ActivityAction::VariablesImported, $environment->name);
        }

        return $result;
    }
}
