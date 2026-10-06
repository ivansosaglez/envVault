<?php

use App\Http\Controllers\Api\EnvironmentController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\VariableController;
use Illuminate\Support\Facades\Route;

Route::post('tokens', [TokenController::class, 'store'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    Route::delete('tokens', [TokenController::class, 'destroy']);

    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::get('projects/{project}/environments', [EnvironmentController::class, 'index']);

    Route::get('environments/{environment}/variables', [VariableController::class, 'index']);
    Route::post('environments/{environment}/variables', [VariableController::class, 'store']);
    Route::patch('variables/{variable}', [VariableController::class, 'update']);
    Route::delete('variables/{variable}', [VariableController::class, 'destroy']);
});
