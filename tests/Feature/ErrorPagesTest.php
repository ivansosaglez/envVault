<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

it('renders a friendly 404 page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/projects/does-not-exist')
        ->assertNotFound()
        ->assertSee("We couldn't find that page")
        ->assertSee('Back to your projects');
});

it('renders a 404 for guests on unknown pages', function () {
    $this->get('/nope')->assertNotFound()->assertSee('Back to home');
});

it('renders a friendly 403 page and never leaks internals', function () {
    Route::get('/_forbidden', fn () => abort(403))->middleware('web');

    $this->get('/_forbidden')->assertForbidden()->assertSee("You don't have access to this");
});

it('renders a generic 500 page without stack traces when debug is off', function () {
    config(['app.debug' => false]);
    Route::get('/_boom', fn () => throw new RuntimeException('database password is hunter2'))->middleware('web');

    $this->get('/_boom')
        ->assertStatus(500)
        ->assertSee('Something went wrong')
        ->assertDontSee('hunter2')
        ->assertDontSee('RuntimeException');
});
