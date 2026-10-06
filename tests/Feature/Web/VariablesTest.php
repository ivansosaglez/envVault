<?php

use App\Livewire\Environments\Show;
use App\Models\Activity;
use App\Models\Environment;
use App\Models\EnvironmentVariable;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function envPage(Environment $environment, ?User $user = null)
{
    return Livewire::actingAs($user ?? $environment->project->user)
        ->test(Show::class, ['projectSlug' => $environment->project->slug, 'environmentSlug' => $environment->slug]);
}

it('shows an empty state for an environment without variables', function () {
    envPage(makeEnvironment())
        ->assertSee('No variables found.')
        ->assertSee('Add your first environment variable');
});

it('adds a variable', function () {
    $environment = makeEnvironment();

    envPage($environment)
        ->call('openCreate')
        ->set('key', 'DB_HOST')
        ->set('value', 'localhost')
        ->call('saveVariable')
        ->assertHasNoErrors()
        ->assertSet('showForm', false);

    $variable = $environment->variables()->first();
    expect($variable->key)->toBe('DB_HOST')->and($variable->value)->toBe('localhost')->and($variable->is_secret)->toBeFalse();
});

it('validates the key format and uniqueness per environment', function (string $key, string $error) {
    $environment = makeEnvironment();
    addVariable($environment, 'EXISTING', '1');

    envPage($environment)->set('key', $key)->call('saveVariable')->assertHasErrors(['key' => $error]);
})->with([
    'empty' => ['', 'required'],
    'has spaces' => ['MY KEY', 'regex'],
    'starts with digit' => ['1KEY', 'regex'],
    'duplicated' => ['EXISTING', 'unique'],
]);

it('allows the same key in different environments', function () {
    $local = makeEnvironment(name: 'Local');
    $production = makeEnvironment($local->project->user, 'My App', 'Staging');
    addVariable($local, 'APP_ENV', 'local');

    envPage($production)->set('key', 'APP_ENV')->set('value', 'staging')->call('saveVariable')->assertHasNoErrors();
});

it('edits a variable', function () {
    $environment = makeEnvironment();
    $variable = addVariable($environment, 'APP_DEBUG', 'true');

    envPage($environment)
        ->call('openEdit', $variable->id)
        ->assertSet('key', 'APP_DEBUG')
        ->assertSet('value', 'true')
        ->set('value', 'false')
        ->call('saveVariable')
        ->assertHasNoErrors();

    expect($variable->fresh()->value)->toBe('false');
});

it('keeps the same key when saving an edit without renaming', function () {
    $environment = makeEnvironment();
    $variable = addVariable($environment, 'APP_DEBUG', 'true');

    envPage($environment)->call('openEdit', $variable->id)->call('saveVariable')->assertHasNoErrors();
});

it('deletes a variable after confirmation', function () {
    $environment = makeEnvironment();
    $variable = addVariable($environment, 'OLD_API_KEY', 'x');

    envPage($environment)
        ->call('confirmDelete', $variable->id)
        ->assertSet('confirmingDelete', true)
        ->call('deleteVariable')
        ->assertSet('confirmingDelete', false);

    expect(EnvironmentVariable::count())->toBe(0);
});

it('stores secrets encrypted and never renders them by default', function () {
    $environment = makeEnvironment();

    envPage($environment)
        ->set('key', 'STRIPE_SECRET')
        ->set('value', 'sk_live_topsecret')
        ->set('isSecret', true)
        ->call('saveVariable');

    $raw = DB::table('environment_variables')->value('value');
    expect($raw)->not->toContain('sk_live_topsecret');

    envPage($environment)
        ->assertSee(EnvironmentVariable::MASK)
        ->assertDontSee('sk_live_topsecret')
        ->assertSee('Show');
});

it('reveals a secret on demand and hides it again', function () {
    $environment = makeEnvironment();
    $secret = addVariable($environment, 'DB_PASSWORD', 'hunter2', true);

    envPage($environment)
        ->call('reveal', $secret->id)
        ->assertSee('hunter2')
        ->call('hide', $secret->id)
        ->assertDontSee('hunter2');
});

it('does not send a secret value to the edit form', function () {
    $environment = makeEnvironment();
    $secret = addVariable($environment, 'DB_PASSWORD', 'hunter2', true);

    envPage($environment)
        ->call('openEdit', $secret->id)
        ->assertSet('value', '')
        ->assertDontSee('hunter2');
});

it('keeps the secret value when only its key is edited', function () {
    $environment = makeEnvironment();
    $secret = addVariable($environment, 'DB_PASSWORD', 'hunter2', true);

    envPage($environment)->call('openEdit', $secret->id)->set('key', 'DB_PASS')->call('saveVariable');
    expect($secret->fresh())->key->toBe('DB_PASS')->value->toBe('hunter2');

    envPage($environment)->call('openEdit', $secret->id)->set('value', 'new-secret')->call('saveVariable');
    expect($secret->fresh()->value)->toBe('new-secret');
});

it('cannot reveal, edit or delete variables of another user', function () {
    $victim = makeEnvironment();
    $secret = addVariable($victim, 'DB_PASSWORD', 'hunter2', true);

    $intruderEnvironment = makeEnvironment();

    // Same component, own environment, but a variable id that belongs to someone else.
    foreach (['reveal', 'openEdit', 'confirmDelete'] as $action) {
        expect(fn () => envPage($intruderEnvironment)->call($action, $secret->id))
            ->toThrow(ModelNotFoundException::class);
    }

    expect($secret->fresh())->not->toBeNull();
});

it('cannot open the page of an environment of another user', function () {
    $victim = makeEnvironment();

    expect(fn () => envPage($victim, User::factory()->create()))->toThrow(ModelNotFoundException::class);
});

it('searches variables by key in real time', function () {
    $environment = makeEnvironment();
    addVariable($environment, 'DB_HOST', 'a');
    addVariable($environment, 'CACHE_STORE', 'b');

    envPage($environment)
        ->set('search', 'db_')
        ->assertSee('DB_HOST')
        ->assertDontSee('CACHE_STORE')
        ->set('search', 'zzz')
        ->assertSee('No variables match');
});

it('sorts variables by key', function () {
    $environment = makeEnvironment();
    addVariable($environment, 'B_KEY');
    addVariable($environment, 'A_KEY');

    envPage($environment)->assertSeeInOrder(['A_KEY', 'B_KEY'])->call('toggleSort')->assertSeeInOrder(['B_KEY', 'A_KEY']);
});

it('paginates long lists', function () {
    $environment = makeEnvironment();
    foreach (range(1, 30) as $i) {
        addVariable($environment, sprintf('VAR_%02d', $i));
    }

    envPage($environment)->assertSee('VAR_01')->assertDontSee('VAR_30');
});

it('logs activity without ever storing values', function () {
    $environment = makeEnvironment();
    $project = $environment->project;

    envPage($environment)->set('key', 'DB_PASSWORD')->set('value', 'super-secret-value')->set('isSecret', true)->call('saveVariable');
    $variable = $environment->variables()->first();
    envPage($environment)->call('openEdit', $variable->id)->set('value', 'another-secret-value')->call('saveVariable');
    envPage($environment)->call('confirmDelete', $variable->id)->call('deleteVariable');

    $entries = Activity::where('project_id', $project->id)->orderBy('id')->get();

    expect($entries->map->description()->all())->toBe(['added DB_PASSWORD', 'updated DB_PASSWORD', 'deleted DB_PASSWORD'])
        ->and($entries->every(fn ($entry) => $entry->user_id === $project->user_id))->toBeTrue()
        ->and(json_encode(DB::table('activities')->get()))->not->toContain('secret-value');
});
