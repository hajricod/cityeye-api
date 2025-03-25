<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class EvidenceDeletionController extends Controller
{
     // Initiate hard delete request
     public function initiateHardDelete($id)
     {
         $user = Auth::user();

         $evidence = Evidence::withTrashed()->findOrFail($id);

         // Set status to In Progress in cache (for 1 minute)
         $statusKey = "evidence_deletion_status_{$id}";
         Cache::put($statusKey, 'In Progress', 60);

         // Simulate async job with delay (replace with real Job dispatching)
         dispatch(function () use ($evidence, $user, $statusKey) {
             sleep(5); // Simulate delay
             try {
                 $evidence->forceDelete();

                 // Log deletion
                 AuditLog::create([
                     'user_id' => $user->id,
                     'action' => 'hard_deleted_evidence',
                     'description' => "Evidence ID {$evidence->id} permanently deleted by {$user->name}"
                 ]);

                 Cache::put($statusKey, 'Completed', 60);
             } catch (\Exception $e) {
                 Cache::put($statusKey, 'Failed', 60);
                 Log::error("Failed to hard delete evidence {$evidence->id}: " . $e->getMessage());
             }
         })->onQueue('deletions');

         return response()->json(['message' => 'Deletion initiated.']);
     }

     // Long polling status check
     public function checkDeletionStatus(Request $request, $id)
     {
         $statusKey = "evidence_deletion_status_{$id}";

         $timeout = 30; // seconds
         $start = now();

         while (now()->diffInSeconds($start) < $timeout) {
             $status = Cache::get($statusKey);

             if (in_array($status, ['Completed', 'Failed'])) {
                 return response()->json(['status' => $status]);
             }

             usleep(500000); // 0.5 second delay
         }

         return response()->json(['status' => 'In Progress']);
     }
}
