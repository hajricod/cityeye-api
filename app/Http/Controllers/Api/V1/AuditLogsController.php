<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogsController extends Controller
{
    public function evidenceLogs()
    {
        // Fetch logs related to evidence actions
        $logs = AuditLog::whereIn('action', [
                'added_evidence',
                'updated_evidence',
                'deleted_evidence',
                'hard_deleted_evidence'
            ])
            ->with('user:id,name,role')  // Get user details
            ->orderBy('created_at', 'desc')
            ->get();

        // Format response
        $response = $logs->map(function ($log) {
            return [
                'user' => [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'role' => $log->user->role,
                ],
                'action' => $log->action,
                'description' => $log->description,
                'timestamp' => $log->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'total_logs' => $response->count(),
            'logs' => $response
        ]);
    }
}
