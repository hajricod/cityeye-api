<?php

use App\Http\Controllers\Api\V1\AuditLogsController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CaseCommentController;
use App\Http\Controllers\Api\V1\CasePersonsController;
use App\Http\Controllers\Api\V1\CasesController;
use App\Http\Controllers\Api\V1\EvidenceController;
use App\Http\Controllers\Api\V1\EvidenceDeletionController;
use App\Http\Controllers\Api\V1\OfficerCasesController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Middleware\BasicAuthMiddleware;
use App\Http\Middleware\CheckRoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('report-crime', [ReportsController::class, 'store']);
    Route::get('report-status/{report_id}', [ReportsController::class, 'status']);
    Route::get('/reports/{report_id}/status', [ReportsController::class, 'getReportStatus']);

    Route::middleware(BasicAuthMiddleware::class)->group(function () {

        Route::post('/cases/{case}/comments', [CaseCommentController::class, 'store']);
        Route::get('/cases/{case}/comments', [CaseCommentController::class, 'index']);
        Route::delete('/cases/{case}/comments/{comment}', [CaseCommentController::class, 'destroy']);

        Route::middleware(CheckRoleMiddleware::class . ':admin')->prefix('admin')->group(function () {
            Route::apiResource('reports', ReportsController::class)->except(['store']);
            Route::apiResource('users', UsersController::class);
            // Route::post('register', [AuthController::class, 'register']);
            Route::put('/users/{id}/role', [UsersController::class, 'updateUserRole']);
            Route::put('/users/{id}/auth_level', [UsersController::class, 'updateUserAuthLevel']);
            Route::get('/audit/evidence-actions', [AuditLogsController::class, 'evidenceLogs']);

            // long polling hard-delete evidence
            Route::post('/evidences/{id}/hard-delete', [EvidenceDeletionController::class, 'initiateHardDelete']);
            Route::get('/evidences/{id}/deletion-status', [EvidenceDeletionController::class, 'checkDeletionStatus']);
            Route::post('/alerts/send', [UsersController::class, 'sendAlert']);
        });

        Route::middleware([CheckRoleMiddleware::class . ':admin,investigator'])->group(function () {

            Route::apiResource('/cases', CasesController::class);
            Route::prefix('/cases')->group(function () {
                Route::get('/{case}/assignees', [CasesController::class, 'assignees']);
                Route::get('/{case}/evidences', [CasesController::class, 'evidences']);
                Route::get('/{case}/suspects', [CasesController::class, 'suspects']);
                Route::get('/{case}/victims', [CasesController::class, 'victims']);
                Route::get('/{case}/witnesses', [CasesController::class, 'witnesses']);
                Route::get('/{id}/report', [CasesController::class, 'generatePdfReport']);
            });

            Route::prefix('cases/{case}/persons')->group(function () {
                Route::get('all', [CasePersonsController::class, 'allPersons']);
                Route::get('{type}', [CasePersonsController::class, 'index']);
                Route::post('{type}', [CasePersonsController::class, 'store']);
                Route::get('{type}/{id}', [CasePersonsController::class, 'show']);
                Route::put('{type}/{id}', [CasePersonsController::class, 'update']);
                Route::delete('{type}/{id}', [CasePersonsController::class, 'destroy']);
            });


            Route::prefix('/evidences')->group(function () {
                Route::get('/text-analysis', [EvidenceController::class, 'textAnalysis']);
                Route::put('/{evidence}', [EvidenceController::class, 'update']);
                Route::delete('/{evidence}/soft-delete', [EvidenceController::class, 'destroy']);
                Route::get('/{evidence}/confirm-delete', [EvidenceController::class, 'confirmDelete']);
                Route::delete('/{evidence}/hard-delete', [EvidenceController::class, 'hardDelete']);
            });
        });

        Route::middleware([CheckRoleMiddleware::class . ':admin,investigator,officer'])->group(function () {
            Route::post('/cases/{case}/evidences', [EvidenceController::class, 'store']);
            Route::get('/evidences/{evidence}/file', [EvidenceController::class, 'download']);
            Route::get('/evidences/{evidence}', [EvidenceController::class, 'show']);
            Route::get('/evidences/{evidence}/image', [EvidenceController::class, 'getImage']);
            Route::get('/cases/{id}/links', [CasesController::class, 'extractLinks']);
        });

        Route::middleware([CheckRoleMiddleware::class . ':officer'])->group(function () {
            Route::get('/officer/my-cases', [OfficerCasesController::class, 'index']);
            Route::put('/officer/my-cases/{id}/status', [OfficerCasesController::class, 'updateStatus']);
        });

    });
});
