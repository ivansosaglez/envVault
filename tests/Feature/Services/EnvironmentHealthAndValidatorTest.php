<?php

use App\Enums\HealthStatus;
use App\Models\Environment;
use App\Models\EnvironmentVariable;
use App\Services\EnvironmentHealth;
use App\Services\EnvParser;
use App\Services\EnvValidator;

function environmentWith(array $pairs): Environment
{
    $environment = Environment::factory()->create();

    foreach ($pairs as $key => $value) {
        EnvironmentVariable::factory()->for($environment)->create(['key' => $key, 'value' => $value]);
    }

    return $environment->load('variables');
}

it('scores an environment against the expected keys', function () {
    $environment = environmentWith(['A' => '1', 'B' => '', 'C' => '3']);

    $report = (new EnvironmentHealth)->check($environment, ['A', 'B', 'C', 'D']);

    expect($report->expected)->toBe(4)
        ->and($report->configured)->toBe(2)
        ->and($report->missing)->toBe(['D'])
        ->and($report->empty)->toBe(['B'])
        ->and($report->percentage())->toBe(50)
        ->and($report->status())->toBe(HealthStatus::Incomplete);
});

it('derives the status from missing and empty variables', function () {
    $health = new EnvironmentHealth;

    expect($health->check(environmentWith(['A' => '1']), ['A'])->status())->toBe(HealthStatus::Healthy)
        ->and($health->check(environmentWith(['A' => '']), ['A'])->status())->toBe(HealthStatus::Incomplete)
        ->and($health->check(environmentWith(array_fill_keys(range('A', 'I'), '1')), [...range('A', 'I'), 'J'])->status())->toBe(HealthStatus::Attention)
        ->and($health->check(environmentWith([]), [])->percentage())->toBe(100);
});

it('builds the expected keys from the union of environments', function () {
    $a = environmentWith(['A' => '1', 'B' => '1']);
    $b = environmentWith(['B' => '1', 'C' => '1']);

    expect((new EnvironmentHealth)->expectedKeys(collect([$a, $b])))->toBe(['A', 'B', 'C']);
});

it('validates a pasted env against an environment', function () {
    $production = environmentWith(['APP_NAME' => 'x', 'APP_ENV' => 'x', 'DB_HOST' => 'x', 'DB_DATABASE' => 'x', 'STRIPE_SECRET' => 'x', 'REDIS_HOST' => 'x']);

    $report = (new EnvValidator(new EnvParser))->validate(<<<'ENV'
    # production
    APP_NAME=My App
    export APP_ENV="production"
    DB_HOST=
    DB_DATABASE=myapp
    LEGACY_KEY=1
    ENV, $production);

    expect($report->missing)->toBe(['REDIS_HOST', 'STRIPE_SECRET'])
        ->and($report->empty)->toBe(['DB_HOST'])
        ->and($report->present)->toBe(['APP_ENV', 'APP_NAME', 'DB_DATABASE'])
        ->and($report->unknown)->toBe(['LEGACY_KEY'])
        ->and($report->isValid())->toBeFalse();
});
