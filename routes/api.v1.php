<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use VmEngine\Example\Http\Controllers\Api\ExampleApiController;

/*
 * Example module API v1 — prefix /api/v1/example, names api.v1.example.*.
 * Token auth via synapps-auth (Bearer access token from POST /api/auth/login).
 */
$stack = ['auth:sanctum', 'api.auth', 'api.token.expiry', 'throttle:api'];

Route::middleware([...$stack, 'api.permission:example.manage.read'])->group(function () {
    Route::get('/examples', [ExampleApiController::class, 'index'])->name('examples.index');
    Route::get('/examples/{id}', [ExampleApiController::class, 'show'])->name('examples.show')->whereNumber('id');
});

Route::middleware([...$stack, 'api.permission:example.category.read'])
    ->get('/categories', [ExampleApiController::class, 'categories'])->name('categories.index');

Route::middleware([...$stack, 'api.permission:example.manage.update'])
    ->patch('/examples/{id}/status', [ExampleApiController::class, 'updateStatus'])->name('examples.status')->whereNumber('id');

Route::middleware([...$stack, 'api.permission:example.manage.create', 'api.signed'])
    ->post('/examples', [ExampleApiController::class, 'store'])->name('examples.store');
