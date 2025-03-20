<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BasicAuthMiddleware;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::middleware(BasicAuthMiddleware::class)->group(function () {

        Route::middleware(AdminMiddleware::class)->prefix('admin')->group(function () {
            Route::apiResource('users', UsersController::class);
            Route::post('register', [AuthController::class, 'register']);
            Route::put('/users/{id}/role', [UsersController::class, 'updateUserRole']);
            Route::put('/users/{id}/auth_level', [UsersController::class, 'updateUserAuthLevel']);
        });

    });
});

Route::middleware(BasicAuthMiddleware::class)->get('/user', function () {
    return response()->json(['message' => 'Authenticated successfully']);
});
