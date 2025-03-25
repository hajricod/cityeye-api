<?php

use App\Http\Controllers\Api\V1\AuditLogsController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CasePersonsController;
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
    Route::get('/reports/{report_id}/status', [ReportsController::class, 'getReportStatus']);

    Route::middleware(BasicAuthMiddleware::class)->group(function () {

        Route::middleware(CheckRoleMiddleware::class . ':admin')->prefix('admin')->group(function () {
            Route::apiResource('reports', ReportsController::class)->except(['store']);
            Route::apiResource('users', UsersController::class);
            Route::post('register', [AuthController::class, 'register']);
            Route::put('/users/{id}/role', [UsersController::class, 'updateUserRole']);
            Route::put('/users/{id}/auth_level', [UsersController::class, 'updateUserAuthLevel']);
            Route::get('/audit/evidence-actions', [AuditLogsController::class, 'evidenceLogs']);
        });

        Route::middleware([CheckRoleMiddleware::class . ':admin,investigator'])->group(function () {
            Route::apiResource('cases', CasesController::class);
            Route::get('/cases/{case}/assignees', [CasesController::class, 'assignees']);
            Route::get('/cases/{case}/evidences', [CasesController::class, 'evidences']);
            Route::get('/cases/{case}/suspects', [CasesController::class, 'suspects']);
            Route::get('/cases/{case}/victims', [CasesController::class, 'victims']);
            Route::get('/cases/{case}/witnesses', [CasesController::class, 'witnesses']);
            Route::get('/evidences/text-analysis', [EvidenceController::class, 'textAnalysis']);
            Route::delete('/evidences/{evidence}/soft-delete', [EvidenceController::class, 'destroy']);

            Route::put('/evidences/{evidence}', [EvidenceController::class, 'update']);
            Route::get('/evidences/{evidence}/confirm-delete', [EvidenceController::class, 'confirmDelete']);
            Route::delete('/evidences/{evidence}/hard-delete', [EvidenceController::class, 'hardDelete']);
            Route::get('/cases/{id}/report', [CasesController::class, 'generatePdfReport']);

            Route::get('/case-persons/{case}', [CasePersonsController::class, 'index']);
            Route::get('/case-persons/{case}/{person}', [CasePersonsController::class, 'show']);
            Route::delete('/case-persons/{case}/{person}', [CasePersonsController::class, 'destroy']);
        });

        Route::middleware([CheckRoleMiddleware::class . ':admin,investigator,officer'])->group(function () {
            Route::post('/cases/{case}/evidences', [EvidenceController::class, 'store']);
            Route::get('/evidences/{evidence}/file', [EvidenceController::class, 'download']);
            Route::get('/evidences/{evidence}', [EvidenceController::class, 'show']);
            Route::get('/evidences/{evidence}/image', [EvidenceController::class, 'getImage']);
            Route::get('/cases/{id}/links', [CasesController::class, 'extractLinks']);
        });


    });
});

Route::middleware(BasicAuthMiddleware::class)->get('/user', function () {
    return response()->json(['message' => 'Authenticated successfully']);
});
