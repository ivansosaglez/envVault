<?php

use App\Livewire\Environments;
use App\Livewire\Projects;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', Projects\Index::class)->name('dashboard');

    Route::get('projects/{projectSlug}', Projects\Show::class)->name('projects.show');
    Route::get('projects/{projectSlug}/compare', Projects\Compare::class)->name('projects.compare');
    Route::get('projects/{projectSlug}/validate', Projects\Validate::class)->name('projects.validate');
    Route::get('projects/{projectSlug}/example', Projects\Example::class)->name('projects.example');
    Route::get('projects/{projectSlug}/environments/{environmentSlug}', Environments\Show::class)->name('projects.environments.show');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
