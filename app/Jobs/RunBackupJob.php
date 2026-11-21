<?php

namespace App\Jobs;

use App\Models\BackupRun;
use App\Services\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Manual "Run backup now" from the Backup Settings page, run off the web request.
 */
class RunBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(public readonly BackupRun $run) {}

    public function handle(BackupService $backups): void
    {
        $run = $this->run->fresh();

        if ($run && $run->status === 'queued') {
            $backups->run($run);
        }
    }
}
