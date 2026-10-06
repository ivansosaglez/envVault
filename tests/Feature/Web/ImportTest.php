<?php

use App\Livewire\Environments\Show;
use App\Models\Activity;
use App\Models\Environment;
use Livewire\Livewire;

function importInto(Environment $environment, string $content)
{
    return Livewire::actingAs($environment->project->user)
        ->test(Show::class, ['projectSlug' => $environment->project->slug, 'environmentSlug' => $environment->slug])
        ->call('openImport')
        ->set('importContent', $content)
        ->call('analyzeImport');
}

it('previews the variables detected before saving anything', function () {
    $environment = makeEnvironment();

    importInto($environment, "APP_NAME=\"My App\"\nAPP_ENV=local\nDB_HOST=localhost\nDB_PASSWORD=secret")
        ->assertSee('4 variables detected')
        ->assertSee('DB_PASSWORD');

    expect($environment->variables()->count())->toBe(0);
});

it('imports new variables and marks likely secrets', function () {
    $environment = makeEnvironment();

    importInto($environment, "APP_NAME=\"My App\"\nAPP_ENV=local\nDB_HOST=localhost\nDB_PASSWORD=secret\nSTRIPE_SECRET=sk_test")
        ->call('confirmImport')
        ->assertSet('showImport', false);

    $variables = $environment->variables()->get()->keyBy('key');

    expect($variables)->toHaveCount(5)
        ->and($variables['APP_NAME']->value)->toBe('My App')
        ->and($variables['DB_PASSWORD']->is_secret)->toBeTrue()
        ->and($variables['STRIPE_SECRET']->is_secret)->toBeTrue()
        ->and($variables['DB_HOST']->is_secret)->toBeFalse();
});

it('skips existing variables by default', function () {
    $environment = makeEnvironment();
    addVariable($environment, 'APP_NAME', 'Original');

    importInto($environment, "APP_NAME=Imported\nAPP_ENV=local")
        ->assertSee('Already exist')
        ->call('confirmImport');

    expect($environment->variables()->where('key', 'APP_NAME')->first()->value)->toBe('Original')
        ->and($environment->variables()->count())->toBe(2);
});

it('replaces an existing variable only when explicitly chosen', function () {
    $environment = makeEnvironment();
    addVariable($environment, 'APP_NAME', 'Original');
    addVariable($environment, 'APP_ENV', 'production');

    importInto($environment, "APP_NAME=Imported\nAPP_ENV=local")
        ->set('replace', ['APP_NAME'])
        ->call('confirmImport');

    expect($environment->variables()->where('key', 'APP_NAME')->first()->value)->toBe('Imported')
        ->and($environment->variables()->where('key', 'APP_ENV')->first()->value)->toBe('production');
});

it('never overwrites a secret silently', function () {
    $environment = makeEnvironment();
    $secret = addVariable($environment, 'DB_PASSWORD', 'original-secret', true);

    importInto($environment, 'DB_PASSWORD=changed')
        ->assertSee('Secret')
        ->assertDontSee('original-secret')
        ->call('confirmImport');

    expect($secret->fresh()->value)->toBe('original-secret');

    importInto($environment, 'DB_PASSWORD=changed')->set('replace', ['DB_PASSWORD'])->call('confirmImport');

    expect($secret->fresh()->value)->toBe('changed')
        ->and($secret->fresh()->is_secret)->toBeTrue();
});

it('imports empty values and uses the last value of duplicated keys', function () {
    $environment = makeEnvironment();

    importInto($environment, "A=\nB=\"\"\nC=1\nC=2")
        ->assertSee('3 variables detected')
        ->assertSee('Duplicated keys')
        ->call('confirmImport');

    $variables = $environment->variables()->get()->pluck('value', 'key');

    expect($variables->all())->toBe(['A' => '', 'B' => '', 'C' => '2']);
});

it('warns when nothing could be detected', function () {
    importInto(makeEnvironment(), "# only a comment\n\n")->assertSee('No variables detected.');
});

it('requires some content', function () {
    Livewire::actingAs(($environment = makeEnvironment())->project->user)
        ->test(Show::class, ['projectSlug' => $environment->project->slug, 'environmentSlug' => $environment->slug])
        ->call('openImport')
        ->call('analyzeImport')
        ->assertHasErrors('importContent');
});

it('logs a single entry per import, without values', function () {
    $environment = makeEnvironment();

    importInto($environment, "A=super-secret\nB=2")->call('confirmImport');

    $entries = Activity::all();
    expect($entries)->toHaveCount(1)
        ->and($entries->first()->description())->toBe('imported variables into "Production"')
        ->and(json_encode($entries))->not->toContain('super-secret');
});
