<?php

use App\Livewire\Environments\Show as EnvironmentPage;
use App\Livewire\Projects\Show;
use App\Models\Environment;
use App\Models\User;
use Livewire\Livewire;

it('creates an environment and redirects to it', function () {
    $project = makeEnvironment(name: 'Local')->project;

    Livewire::actingAs($project->user)->test(Show::class, ['projectSlug' => $project->slug])
        ->set('environmentName', 'Staging')
        ->set('environmentColor', 'amber')
        ->call('addEnvironment')
        ->assertHasNoErrors()
        ->assertRedirect(route('projects.environments.show', [$project->slug, 'staging']));

    expect($project->environments()->pluck('name')->all())->toBe(['Local', 'Staging']);
});

it('does not allow duplicated environment names in the same project', function () {
    $project = makeEnvironment(name: 'Production')->project;

    Livewire::actingAs($project->user)->test(Show::class, ['projectSlug' => $project->slug])
        ->set('environmentName', 'Production')
        ->call('addEnvironment')
        ->assertHasErrors('environmentName');

    expect($project->environments()->count())->toBe(1);
});

it('rejects names that would collide on the slug', function () {
    $project = makeEnvironment(name: 'Pre Prod')->project;

    Livewire::actingAs($project->user)->test(Show::class, ['projectSlug' => $project->slug])
        ->set('environmentName', 'Pre-Prod')
        ->call('addEnvironment')
        ->assertHasErrors('environmentName');
});

it('allows the same environment name in different projects', function () {
    $user = User::factory()->create();
    makeEnvironment($user, 'App One', 'Production');
    $other = makeEnvironment($user, 'App Two', 'Local')->project;

    Livewire::actingAs($user)->test(Show::class, ['projectSlug' => $other->slug])
        ->set('environmentName', 'Production')
        ->call('addEnvironment')
        ->assertHasNoErrors();
});

it('validates the environment color', function () {
    $project = makeEnvironment()->project;

    Livewire::actingAs($project->user)->test(Show::class, ['projectSlug' => $project->slug])
        ->set('environmentName', 'QA')
        ->set('environmentColor', 'javascript:alert(1)')
        ->call('addEnvironment')
        ->assertHasErrors('environmentColor');
});

it('deletes an environment', function () {
    $environment = makeEnvironment();
    $project = $environment->project;

    Livewire::actingAs($project->user)->test(EnvironmentPage::class, ['projectSlug' => $project->slug, 'environmentSlug' => $environment->slug])
        ->call('deleteEnvironment')
        ->assertRedirect(route('projects.show', $project->slug));

    expect(Environment::count())->toBe(0);
});

it('does not expose environments to other users', function () {
    $environment = makeEnvironment();
    $url = route('projects.environments.show', [$environment->project->slug, $environment->slug]);

    $this->actingAs($environment->project->user)->get($url)->assertOk();
    $this->actingAs(User::factory()->create())->get($url)->assertNotFound();
});

it('does not resolve an environment through another project', function () {
    $mine = makeEnvironment(name: 'Local');
    $theirs = makeEnvironment(name: 'Production');

    $this->actingAs($mine->project->user)
        ->get(route('projects.environments.show', [$mine->project->slug, $theirs->slug]))
        ->assertNotFound();
});
