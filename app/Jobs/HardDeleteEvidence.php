<?php

namespace App\Jobs;

use App\Models\Evidence;
use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HardDeleteEvidence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $evidence;
    protected $user;
    protected $statusKey;

    public function __construct(Evidence $evidence, $user, $statusKey)
    {
        $this->evidence = $evidence;
        $this->user = $user;
        $this->statusKey = $statusKey;
    }

    public function handle()
    {
        try {
            $this->evidence->forceDelete();

            AuditLog::create([
                'user_id' => $this->user->id,
                'action' => 'hard_deleted_evidence',
                'description' => "Evidence ID {$this->evidence->id} permanently deleted by {$this->user->name}",
            ]);

            Cache::put($this->statusKey, 'Completed', 60);
        } catch (\Exception $e) {
            Cache::put($this->statusKey, 'Failed', 60);
            Log::error("Failed to delete evidence {$this->evidence->id}: " . $e->getMessage());
        }
    }
}

