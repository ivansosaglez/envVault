<?php

use App\Models\Activity;
use App\Models\EnvironmentVariable;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

function apiUser(?User $user = null): User
{
    $user ??= User::factory()->create();
    Sanctum::actingAs($user);

    return $user;
}

// ----- Authentication -----

it('requires authentication on every endpoint', function (string $method, string $uri) {
    $this->json($method, $uri)->assertUnauthorized()->assertJsonStructure(['message']);
})->with([
    ['GET', '/api/projects'],
    ['POST', '/api/projects'],
    ['GET', '/api/projects/1'],
    ['GET', '/api/projects/1/environments'],
    ['GET', '/api/environments/1/variables'],
    ['POST', '/api/environments/1/variables'],
    ['PATCH', '/api/variables/1'],
    ['DELETE', '/api/variables/1'],
]);

it('issues and revokes tokens', function () {
    $user = User::factory()->create(['password' => 'password']);

    $token = $this->postJson('/api/tokens', ['email' => $user->email, 'password' => 'password', 'device_name' => 'cli'])
        ->assertCreated()->json('token');

    $this->withToken($token)->getJson('/api/projects')->assertOk();

    $this->withToken($token)->deleteJson('/api/tokens')->assertNoContent();
    expect($user->tokens()->count())->toBe(0);
});

it('rejects wrong credentials when requesting a token', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->postJson('/api/tokens', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnprocessable()->assertJsonValidationErrors('email');
});

// ----- Projects -----

it('lists only the projects of the authenticated user', function () {
    $user = apiUser();
    makeEnvironment($user, 'Mine');
    makeEnvironment(User::factory()->create(), 'Theirs');

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Mine')
        ->assertJsonPath('data.0.environments_count', 1)
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'description', 'created_at']], 'links', 'meta']);
});

it('creates a project', function () {
    $user = apiUser();

    $this->postJson('/api/projects', ['name' => 'API Project', 'description' => 'Made via API'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'API Project')
        ->assertJsonPath('data.slug', 'api-project');

    expect($user->projects()->count())->toBe(1);
});

it('validates project creation with a consistent error format', function () {
    apiUser();

    $this->postJson('/api/projects', [])->assertUnprocessable()->assertJsonValidationErrors('name')->assertJsonStructure(['message', 'errors']);
});

it('shows a project and its environments', function () {
    $user = apiUser();
    $environment = makeEnvironment($user);
    $project = $environment->project;

    $this->getJson("/api/projects/{$project->id}")->assertOk()->assertJsonPath('data.id', $project->id);
    $this->getJson("/api/projects/{$project->id}/environments")
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Production')
        ->assertJsonPath('data.0.variables_count', 0);
});

it('returns 403 for projects of other users and 404 for unknown ones', function () {
    apiUser();
    $project = makeEnvironment()->project;

    $this->getJson("/api/projects/{$project->id}")->assertForbidden()->assertExactJson(['message' => 'This action is unauthorized.']);
    $this->getJson("/api/projects/{$project->id}/environments")->assertForbidden();
    $this->getJson('/api/projects/999999')->assertNotFound()->assertExactJson(['message' => 'Resource not found.']);
});

// ----- Variables -----

it('lists variables, supports search and never exposes secret values', function () {
    $user = apiUser();
    $environment = makeEnvironment($user);
    addVariable($environment, 'DB_HOST', 'localhost');
    addVariable($environment, 'DB_PASSWORD', 'hunter2', true);
    addVariable($environment, 'APP_NAME', 'x');

    $response = $this->getJson("/api/environments/{$environment->id}/variables")->assertOk()->assertJsonCount(3, 'data');

    expect($response->json('data.0.key'))->toBe('APP_NAME')
        ->and($response->getContent())->not->toContain('hunter2');

    $this->getJson("/api/environments/{$environment->id}/variables?search=db_")->assertJsonCount(2, 'data');

    $secret = collect($response->json('data'))->firstWhere('key', 'DB_PASSWORD');
    expect($secret)->toMatchArray(['value' => null, 'is_secret' => true, 'has_value' => true]);
});

it('creates a variable', function () {
    $environment = makeEnvironment(apiUser());

    $this->postJson("/api/environments/{$environment->id}/variables", ['key' => 'APP_ENV', 'value' => 'production'])
        ->assertCreated()
        ->assertJsonPath('data.key', 'APP_ENV')
        ->assertJsonPath('data.value', 'production')
        ->assertJsonPath('data.is_secret', false);
});

it('creates a secret variable, encrypted at rest and masked in the response', function () {
    $environment = makeEnvironment(apiUser());

    $response = $this->postJson("/api/environments/{$environment->id}/variables", ['key' => 'STRIPE_SECRET', 'value' => 'sk_live_xyz', 'is_secret' => true])
        ->assertCreated()
        ->assertJsonPath('data.value', null);

    expect($response->getContent())->not->toContain('sk_live_xyz')
        ->and(DB::table('environment_variables')->value('value'))->not->toContain('sk_live_xyz')
        ->and(EnvironmentVariable::first()->value)->toBe('sk_live_xyz');
});

it('flags likely secrets automatically when is_secret is omitted', function () {
    $environment = makeEnvironment(apiUser());

    $this->postJson("/api/environments/{$environment->id}/variables", ['key' => 'DB_PASSWORD', 'value' => 'x'])
        ->assertJsonPath('data.is_secret', true);
});

it('validates variable input', function (array $payload, string $field) {
    $environment = makeEnvironment(apiUser());
    addVariable($environment, 'EXISTING');

    $this->postJson("/api/environments/{$environment->id}/variables", $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'missing key' => [[], 'key'],
    'invalid key' => [['key' => 'not valid'], 'key'],
    'duplicated key' => [['key' => 'EXISTING'], 'key'],
    'bad secret flag' => [['key' => 'A', 'is_secret' => 'maybe'], 'is_secret'],
]);

it('updates a variable', function () {
    $environment = makeEnvironment(apiUser());
    $variable = addVariable($environment, 'APP_DEBUG', 'true');

    $this->patchJson("/api/variables/{$variable->id}", ['value' => 'false'])
        ->assertOk()
        ->assertJsonPath('data.value', 'false')
        ->assertJsonPath('data.key', 'APP_DEBUG');

    expect($variable->fresh()->value)->toBe('false');
});

it('keeps the value when updating only other fields', function () {
    $environment = makeEnvironment(apiUser());
    $variable = addVariable($environment, 'DB_PASSWORD', 'hunter2', true);

    $this->patchJson("/api/variables/{$variable->id}", ['key' => 'DB_PASS'])->assertOk()->assertJsonPath('data.value', null);

    expect($variable->fresh())->key->toBe('DB_PASS')->value->toBe('hunter2');
});

it('does not allow renaming a variable to an existing key', function () {
    $environment = makeEnvironment(apiUser());
    addVariable($environment, 'TAKEN');
    $variable = addVariable($environment, 'OTHER');

    $this->patchJson("/api/variables/{$variable->id}", ['key' => 'TAKEN'])->assertUnprocessable()->assertJsonValidationErrors('key');
    $this->patchJson("/api/variables/{$variable->id}", ['key' => 'OTHER'])->assertOk();
});

it('deletes a variable', function () {
    $environment = makeEnvironment(apiUser());
    $variable = addVariable($environment, 'OLD_KEY');

    $this->deleteJson("/api/variables/{$variable->id}")->assertNoContent();

    expect(EnvironmentVariable::count())->toBe(0);
});

it('forbids touching variables and environments of other users', function () {
    apiUser();
    $victim = makeEnvironment();
    $variable = addVariable($victim, 'DB_PASSWORD', 'hunter2', true);

    $this->getJson("/api/environments/{$victim->id}/variables")->assertForbidden();
    $this->postJson("/api/environments/{$victim->id}/variables", ['key' => 'HACK', 'value' => 'x'])->assertForbidden();
    $this->patchJson("/api/variables/{$variable->id}", ['value' => 'pwned'])->assertForbidden();
    $this->deleteJson("/api/variables/{$variable->id}")->assertForbidden();

    expect($variable->fresh()->value)->toBe('hunter2')
        ->and($victim->variables()->count())->toBe(1);
});

it('records API changes in the activity log without values', function () {
    $user = apiUser();
    $environment = makeEnvironment($user);

    $this->postJson("/api/environments/{$environment->id}/variables", ['key' => 'API_TOKEN', 'value' => 'tok_123']);
    $variable = $environment->variables()->first();
    $this->patchJson("/api/variables/{$variable->id}", ['value' => 'tok_456']);
    $this->deleteJson("/api/variables/{$variable->id}");

    expect(Activity::orderBy('id')->get()->map->description()->all())->toBe(['added API_TOKEN', 'updated API_TOKEN', 'deleted API_TOKEN'])
        ->and(json_encode(DB::table('activities')->get()))->not->toContain('tok_');
});

it('does not leak model names in error responses', function () {
    apiUser();

    $this->getJson('/api/environments/999999/variables')->assertNotFound()->assertExactJson(['message' => 'Resource not found.']);
    $this->getJson('/api/does-not-exist')->assertNotFound()->assertExactJson(['message' => 'Resource not found.']);
});
