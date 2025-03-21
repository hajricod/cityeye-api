<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BasicAuthMiddleware;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::post('report-crime', [ReportsController::class, 'store']);
    Route::get('report-status/{report_id}', [ReportsController::class, 'status']);

    Route::middleware(BasicAuthMiddleware::class)->group(function () {

        Route::middleware(AdminMiddleware::class)->prefix('admin')->group(function () {
            Route::apiResource('reports', ReportsController::class)->except(['store']);
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
