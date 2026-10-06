<?php

use App\Livewire\Projects\Index;
use App\Livewire\Projects\Show;
use App\Models\Activity;
use App\Models\EnvironmentVariable;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/projects/anything')->assertRedirect('/login');
});

it('shows an empty state when there are no projects', function () {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('No projects yet.')
        ->assertSee('Create your first project to start managing your environments.');
});

it('lists the projects of the user with their health', function () {
    $user = User::factory()->create();
    $production = makeEnvironment($user, 'My App', 'Production');
    $staging = makeEnvironment($user, 'My App', 'Staging');
    addVariable($production, 'APP_ENV', 'production');
    addVariable($production, 'REDIS_HOST', 'redis');
    addVariable($staging, 'APP_ENV', 'staging');
    makeEnvironment(User::factory()->create(), 'Someone Else Project');

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('My App')
        ->assertSeeInOrder(['2', 'environments'])
        ->assertSee('✓ Complete')
        ->assertSee('1 missing')
        ->assertDontSee('Someone Else Project');
});

it('creates a project and records it in the activity log', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Index::class)
        ->set('name', 'My Laravel App')
        ->set('description', 'Laravel application')
        ->call('create')
        ->assertRedirect(route('projects.show', 'my-laravel-app'));

    $project = $user->projects()->first();
    expect($project->name)->toBe('My Laravel App')
        ->and(Activity::where('project_id', $project->id)->first()->description())->toBe('created the project "My Laravel App"');
});

it('requires a project name', function () {
    Livewire::actingAs(User::factory()->create())->test(Index::class)
        ->set('name', '')
        ->call('create')
        ->assertHasErrors(['name' => 'required']);
});

it('gives projects with the same name a unique slug', function () {
    $user = User::factory()->create();

    foreach (range(1, 2) as $_) {
        Livewire::actingAs($user)->test(Index::class)->set('name', 'Same Name')->call('create');
    }

    expect($user->projects()->pluck('slug')->all())->toBe(['same-name', 'same-name-2']);
});

it('updates a project', function () {
    $environment = makeEnvironment();
    $project = $environment->project;

    Livewire::actingAs($project->user)->test(Show::class, ['projectSlug' => $project->slug])
        ->set('name', 'Renamed')
        ->set('description', 'New description')
        ->call('updateProject')
        ->assertHasNoErrors();

    expect($project->fresh())->name->toBe('Renamed')->description->toBe('New description')->slug->toBe($project->slug);
});

it('deletes a project with everything inside it', function () {
    $environment = makeEnvironment();
    addVariable($environment, 'APP_ENV', 'x');
    $project = $environment->project;

    Livewire::actingAs($project->user)->test(Show::class, ['projectSlug' => $project->slug])
        ->call('deleteProject')
        ->assertRedirect(route('dashboard'));

    expect(Project::count())->toBe(0)->and(EnvironmentVariable::count())->toBe(0);
});

it('hides projects that belong to someone else', function () {
    $project = makeEnvironment()->project;
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('projects.show', $project->slug))->assertNotFound();
    $this->actingAs($intruder)->get(route('projects.compare', $project->slug))->assertNotFound();
    $this->actingAs($intruder)->get(route('projects.validate', $project->slug))->assertNotFound();
    $this->actingAs($intruder)->get(route('projects.example', $project->slug))->assertNotFound();
});

it('summarizes synchronization across environments on the project page', function () {
    $user = User::factory()->create();
    $local = makeEnvironment($user, 'My App', 'Local');
    $production = makeEnvironment($user, 'My App', 'Production');
    addVariable($local, 'SAME', '1');
    addVariable($production, 'SAME', '1');
    addVariable($local, 'DIFF', 'a');
    addVariable($production, 'DIFF', 'b');
    addVariable($local, 'ONLY_LOCAL', 'x');

    $this->actingAs($user)->get(route('projects.show', 'my-app'))
        ->assertOk()
        ->assertSeeInOrder(['Variables', '3', 'Synchronized', '1', 'Missing', '1', 'Different', '1']);
});
