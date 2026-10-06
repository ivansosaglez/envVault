<?php

use App\Models\Environment;
use App\Models\EnvironmentVariable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

it('generates a unique slug per user', function () {
    $user = User::factory()->create();

    $first = Project::factory()->for($user)->create(['name' => 'My App']);
    $second = Project::factory()->for($user)->create(['name' => 'My App']);
    $other = Project::factory()->create(['name' => 'My App']);

    expect($first->slug)->toBe('my-app')
        ->and($second->slug)->toBe('my-app-2')
        ->and($other->slug)->toBe('my-app');
});

it('does not allow duplicated environment names within a project', function () {
    $environment = Environment::factory()->create(['name' => 'Production']);

    expect(fn () => Environment::factory()->for($environment->project)->create(['name' => 'Production']))
        ->toThrow(QueryException::class);
});

it('does not allow duplicated variable keys within an environment', function () {
    $environment = Environment::factory()->create();
    EnvironmentVariable::factory()->for($environment)->create(['key' => 'APP_ENV']);

    expect(fn () => EnvironmentVariable::factory()->for($environment)->create(['key' => 'APP_ENV']))
        ->toThrow(QueryException::class);
});

it('stores variable values encrypted at rest', function () {
    $variable = EnvironmentVariable::factory()->secret()->create(['value' => 'sk_live_super_secret']);

    $raw = DB::table('environment_variables')->where('id', $variable->id)->value('value');

    expect($raw)->not->toContain('sk_live_super_secret')
        ->and(Crypt::decryptString($raw))->toBe('sk_live_super_secret')
        ->and($variable->fresh()->value)->toBe('sk_live_super_secret');
});

it('masks secrets and never serializes the value', function () {
    $secret = EnvironmentVariable::factory()->secret()->create(['value' => 'hunter2']);
    $plain = EnvironmentVariable::factory()->create(['value' => 'localhost']);

    expect($secret->displayValue())->toBe(EnvironmentVariable::MASK)
        ->and($plain->displayValue())->toBe('localhost')
        ->and($secret->toArray())->not->toHaveKey('value')
        ->and($secret->toJson())->not->toContain('hunter2');
});

it('does not mask an empty secret', function () {
    $secret = EnvironmentVariable::factory()->secret()->create(['value' => '']);

    expect($secret->displayValue())->toBe('');
});

it('searches keys case-insensitively and treats wildcards literally', function () {
    $environment = Environment::factory()->create();
    EnvironmentVariable::factory()->for($environment)->create(['key' => 'DB_HOST']);
    EnvironmentVariable::factory()->for($environment)->create(['key' => 'APP_NAME']);

    expect(EnvironmentVariable::search('db_')->count())->toBe(1)
        ->and(EnvironmentVariable::search('%')->count())->toBe(0)
        ->and(EnvironmentVariable::search('')->count())->toBe(2);
});

it('only lets the owner touch projects, environments and variables', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $variable = EnvironmentVariable::factory()
        ->for(Environment::factory()->for(Project::factory()->for($owner)))
        ->secret()->create();

    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::forUser($owner)->allows($ability, $variable->environment->project))->toBeTrue()
            ->and(Gate::forUser($intruder)->denies($ability, $variable->environment->project))->toBeTrue()
            ->and(Gate::forUser($intruder)->denies($ability, $variable->environment))->toBeTrue()
            ->and(Gate::forUser($intruder)->denies($ability, $variable))->toBeTrue();
    }

    expect(Gate::forUser($owner)->allows('reveal', $variable))->toBeTrue()
        ->and(Gate::forUser($intruder)->denies('reveal', $variable))->toBeTrue();
});

it('cascades deletes down the hierarchy', function () {
    $variable = EnvironmentVariable::factory()->create();

    $variable->environment->project->delete();

    expect(Environment::count())->toBe(0)
        ->and(EnvironmentVariable::count())->toBe(0);
});
