<?php

use App\Models\User;

it('sends security headers on every response', function () {
    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'same-origin');
});

it('prevents caching of authenticated pages', function () {
    $response = $this->actingAs(User::factory()->create())->get('/dashboard');

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('redirects guests away from every private page', function (string $uri) {
    $this->get($uri)->assertRedirect('/login');
})->with(['/dashboard', '/profile', '/projects/x', '/projects/x/compare', '/projects/x/validate', '/projects/x/example', '/projects/x/environments/y']);
