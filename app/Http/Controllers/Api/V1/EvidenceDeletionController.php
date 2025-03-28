<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\HardDeleteEvidence;
use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class EvidenceDeletionController extends Controller
{
    /**
     * POST evidence hard-delete.
     */
    public function initiateHardDelete($id)
    {
        $user = Auth::user();

        $evidence = Evidence::withTrashed()->findOrFail($id);

        // Set status to In Progress in cache
        $statusKey = "evidence_deletion_status_{$id}";
        Cache::put($statusKey, 'In Progress', 60);

        // Dispatch job to handle deletion
        HardDeleteEvidence::dispatch($evidence, $user, $statusKey)->onQueue('deletions');

        return response()->json(['message' => 'Deletion initiated.']);
    }

    /**
     * GET evidence hard-delete status.
     */
    public function checkDeletionStatus(Request $request, $id)
    {
        $statusKey = "evidence_deletion_status_{$id}";

        $timeout = 30; // seconds
        $interval = 0.5; // seconds
        $startTime = microtime(true);

        while ((microtime(true) - $startTime) < $timeout) {
            $status = Cache::get($statusKey);

            if ($status === 'Completed' || $status === 'Failed') {
                Log::info("Deletion status for evidence {$id}: {$status}");
                return response()->json(['status' => $status]);
            }

            usleep($interval * 1_000_000); // 0.5 second delay
        }

        // Return current status or fallback after timeout
        $currentStatus = Cache::get($statusKey) ?? 'Unknown';
        Log::info("Polling timeout for evidence {$id}. Last known status: {$currentStatus}");

        return response()->json([
            'status' => $currentStatus,
            'message' => 'Polling timed out without status change.'
        ]);
    }
}
