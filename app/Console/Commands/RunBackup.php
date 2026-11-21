<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Create a zip backup of the database, uploaded files and logs as set in Backup Settings';

    public function handle(BackupService $backups): int
    {
        $run = $backups->run(BackupRun::create(['trigger' => 'scheduled', 'status' => 'queued']));

        if ($run->status !== 'completed') {
            $this->error('Backup failed: '.$run->error);

            return self::FAILURE;
        }

        $this->info("Backup {$run->file_name} written ({$run->sizeLabel()}).");

        return self::SUCCESS;
    }
}
