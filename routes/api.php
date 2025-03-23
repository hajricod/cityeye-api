<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CasesController;
use App\Http\Controllers\Api\V1\EvidenceController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Middleware\BasicAuthMiddleware;
use App\Http\Middleware\CheckRoleMiddleware;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::post('report-crime', [ReportsController::class, 'store']);
    Route::get('report-status/{report_id}', [ReportsController::class, 'status']);

    Route::middleware(BasicAuthMiddleware::class)->group(function () {

        Route::middleware(CheckRoleMiddleware::class . ':admin')->prefix('admin')->group(function () {
            Route::apiResource('reports', ReportsController::class)->except(['store']);
            Route::apiResource('users', UsersController::class);
            Route::post('register', [AuthController::class, 'register']);
            Route::put('/users/{id}/role', [UsersController::class, 'updateUserRole']);
            Route::put('/users/{id}/auth_level', [UsersController::class, 'updateUserAuthLevel']);
        });

        Route::middleware([CheckRoleMiddleware::class . ':admin,investigator'])->group(function () {
            Route::apiResource('cases', CasesController::class);
            Route::get('/cases/{case}/assignees', [CasesController::class, 'assignees']);
            Route::get('/cases/{case}/evidences', [CasesController::class, 'evidences']);
            Route::get('/cases/{case}/suspects', [CasesController::class, 'suspects']);
            Route::get('/cases/{case}/victims', [CasesController::class, 'victims']);
            Route::get('/cases/{case}/witnesses', [CasesController::class, 'witnesses']);
        });

        Route::middleware([CheckRoleMiddleware::class . ':admin,investigator,officer'])->group(function () {
            Route::post('/cases/{case}/evidences', [EvidenceController::class, 'store']);
        });


    });
});

Route::middleware(BasicAuthMiddleware::class)->get('/user', function () {
    return response()->json(['message' => 'Authenticated successfully']);
});
