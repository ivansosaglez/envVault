<?php

use App\Livewire\Projects\Compare;
use App\Livewire\Projects\Example;
use App\Livewire\Projects\Validate;
use App\Models\EnvironmentVariable;
use App\Models\User;
use Livewire\Livewire;

function projectWithTwoEnvironments(): array
{
    $local = makeEnvironment(name: 'Local');
    $production = makeEnvironment($local->project->user, 'My App', 'Production');

    addVariable($local, 'APP_ENV', 'local');
    addVariable($production, 'APP_ENV', 'production');
    addVariable($local, 'APP_NAME', 'Same');
    addVariable($production, 'APP_NAME', 'Same');
    addVariable($local, 'REDIS_HOST', 'redis.internal.test');
    addVariable($production, 'SENTRY_DSN', 'https://sentry.example');
    addVariable($local, 'STRIPE_SECRET', 'sk_test_local', true);
    addVariable($production, 'STRIPE_SECRET', 'sk_live_prod', true);

    return [$local, $production];
}

// ----- Compare -----

it('compares two environments and shows same, different, missing and extra', function () {
    [$local, $production] = projectWithTwoEnvironments();

    $this->actingAs($local->project->user)
        ->get(route('projects.compare', ['projectSlug' => 'my-app', 'left' => 'local', 'right' => 'production']))
        ->assertOk()
        ->assertSee('Missing in Production')
        ->assertSee('Only in Production')
        ->assertSee('✗ missing')
        ->assertSee('production')
        ->assertSee('SENTRY_DSN');
});

it('never reveals secrets in the comparison', function () {
    [$local] = projectWithTwoEnvironments();

    $this->actingAs($local->project->user)
        ->get(route('projects.compare', ['projectSlug' => 'my-app', 'left' => 'local', 'right' => 'production']))
        ->assertSee('STRIPE_SECRET')
        ->assertSee(EnvironmentVariable::MASK)
        ->assertDontSee('sk_test_local')
        ->assertDontSee('sk_live_prod');
});

it('filters the comparison by status', function () {
    [$local] = projectWithTwoEnvironments();

    Livewire::actingAs($local->project->user)
        ->test(Compare::class, ['projectSlug' => 'my-app'])
        ->assertSet('left', 'local')
        ->assertSet('right', 'production')
        ->call('filter', 'missing')
        ->assertSee('REDIS_HOST')
        ->assertDontSee('SENTRY_DSN')
        ->call('swap')
        ->assertSet('left', 'production');
});

it('asks for a second environment when the project only has one', function () {
    $environment = makeEnvironment();

    $this->actingAs($environment->project->user)->get(route('projects.compare', 'my-app'))
        ->assertOk()->assertSee('You need two environments to compare.');
});

// ----- Validator -----

it('validates a pasted env against an environment', function () {
    $production = makeEnvironment(name: 'Production');
    foreach (['APP_NAME', 'APP_ENV', 'DB_HOST', 'DB_DATABASE', 'STRIPE_SECRET', 'REDIS_HOST'] as $key) {
        addVariable($production, $key, 'x');
    }

    Livewire::actingAs($production->project->user)
        ->test(Validate::class, ['projectSlug' => 'my-app'])
        ->set('environment', 'production')
        ->set('content', "# comment\nAPP_NAME=My App\nexport APP_ENV=\"production\"\nDB_HOST=\nDB_DATABASE=myapp\n")
        ->call('validateEnv')
        ->assertSee('Configuration issues')
        ->assertSeeInOrder(['Missing', 'REDIS_HOST', 'STRIPE_SECRET'])
        ->assertSeeInOrder(['Empty', 'DB_HOST'])
        ->assertSeeInOrder(['Present', 'APP_ENV', 'APP_NAME', 'DB_DATABASE']);
});

it('reports a valid configuration', function () {
    $production = makeEnvironment(name: 'Production');
    addVariable($production, 'APP_ENV', 'x');

    Livewire::actingAs($production->project->user)
        ->test(Validate::class, ['projectSlug' => 'my-app'])
        ->set('content', 'APP_ENV=production')
        ->call('validateEnv')
        ->assertSee('Valid for Production');
});

it('requires content to validate and does not store it', function () {
    $production = makeEnvironment(name: 'Production');
    addVariable($production, 'APP_ENV', 'x');

    Livewire::actingAs($production->project->user)
        ->test(Validate::class, ['projectSlug' => 'my-app'])
        ->call('validateEnv')
        ->assertHasErrors('content');

    expect($production->variables()->count())->toBe(1);
});

// ----- .env.example -----

it('generates a .env.example with names only', function () {
    [$local] = projectWithTwoEnvironments();

    $this->actingAs($local->project->user)
        ->get(route('projects.example', 'my-app'))
        ->assertOk()
        ->assertSee('APP_ENV=', false)
        ->assertSee('STRIPE_SECRET=', false)
        ->assertDontSee('sk_test_local')
        ->assertDontSee('redis.internal.test')
        ->assertDontSee('sentry.example');
});

it('can limit the example to one environment', function () {
    [$local] = projectWithTwoEnvironments();

    Livewire::actingAs($local->project->user)
        ->test(Example::class, ['projectSlug' => 'my-app'])
        ->set('environment', 'local')
        ->assertSee('REDIS_HOST=', false)
        ->assertDontSee('SENTRY_DSN=', false);
});

it('downloads the example as .env.example', function () {
    [$local] = projectWithTwoEnvironments();

    Livewire::actingAs($local->project->user)
        ->test(Example::class, ['projectSlug' => 'my-app'])
        ->call('download')
        ->assertFileDownloaded('.env.example', "APP_ENV=\nAPP_NAME=\n\nREDIS_HOST=\n\nSENTRY_DSN=\n\nSTRIPE_SECRET=\n");
});

it('shows an empty state when there is nothing to generate', function () {
    $environment = makeEnvironment();

    $this->actingAs($environment->project->user)->get(route('projects.example', 'my-app'))
        ->assertSee('Nothing to generate yet.');
});

it('protects every tool page from other users', function (string $route) {
    $project = makeEnvironment()->project;

    $this->actingAs(User::factory()->create())->get(route($route, $project->slug))->assertNotFound();
})->with(['projects.compare', 'projects.validate', 'projects.example']);
