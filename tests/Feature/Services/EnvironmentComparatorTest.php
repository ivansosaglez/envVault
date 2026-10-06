<?php

use App\Enums\VariableStatus;
use App\Models\EnvironmentVariable;
use App\Services\EnvironmentComparator;

function vars(array $pairs, array $secrets = []): array
{
    return collect($pairs)->map(fn ($value, $key) => new EnvironmentVariable([
        'key' => $key,
        'value' => $value,
        'is_secret' => in_array($key, $secrets, true),
    ]))->values()->all();
}

it('classifies variables as same, different, missing and extra', function () {
    $result = (new EnvironmentComparator)->compare(
        vars(['APP_NAME' => 'App', 'APP_ENV' => 'local', 'REDIS_HOST' => 'localhost']),
        vars(['APP_NAME' => 'App', 'APP_ENV' => 'production', 'SENTRY_DSN' => 'x']),
    );

    $statuses = collect($result->rows)->mapWithKeys(fn ($row) => [$row->key => $row->status]);

    expect($statuses->all())->toBe([
        'APP_ENV' => VariableStatus::Different,
        'APP_NAME' => VariableStatus::Same,
        'REDIS_HOST' => VariableStatus::Missing,
        'SENTRY_DSN' => VariableStatus::Extra,
    ])->and($result->total())->toBe(4)
        ->and($result->count(VariableStatus::Same))->toBe(1)
        ->and($result->count(VariableStatus::Missing))->toBe(1)
        ->and($result->inSync())->toBeFalse();
});

it('detects secrets that differ without needing to reveal them', function () {
    $result = (new EnvironmentComparator)->compare(
        vars(['STRIPE_SECRET' => 'sk_test', 'MAIL_PASSWORD' => 'same'], ['STRIPE_SECRET', 'MAIL_PASSWORD']),
        vars(['STRIPE_SECRET' => 'sk_live', 'MAIL_PASSWORD' => 'same'], ['STRIPE_SECRET', 'MAIL_PASSWORD']),
    );

    expect($result->only(VariableStatus::Different)[0]->key)->toBe('STRIPE_SECRET')
        ->and($result->only(VariableStatus::Same)[0]->key)->toBe('MAIL_PASSWORD')
        ->and($result->rows[0]->isSecret())->toBeTrue();
});

it('treats an empty value and a missing key as different things', function () {
    $result = (new EnvironmentComparator)->compare(vars(['A' => '', 'B' => '']), vars(['A' => '']));

    expect($result->rows[0]->status)->toBe(VariableStatus::Same)
        ->and($result->rows[1]->status)->toBe(VariableStatus::Missing);
});

it('reports in sync when both sides are identical and handles empty input', function () {
    $comparator = new EnvironmentComparator;

    expect($comparator->compare(vars(['A' => '1']), vars(['A' => '1']))->inSync())->toBeTrue()
        ->and($comparator->compare([], [])->total())->toBe(0);
});

it('sorts rows by key', function () {
    $result = (new EnvironmentComparator)->compare(vars(['Z' => '1', 'B' => '1']), vars(['M' => '1']));

    expect(array_map(fn ($row) => $row->key, $result->rows))->toBe(['B', 'M', 'Z']);
});
