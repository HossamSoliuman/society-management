<?php

namespace App\Services;

use App\Models\BackupRun;
use App\Models\BackupSetting;
use App\Models\User;
use App\Notifications\BackupCompleted;
use FilesystemIterator;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;
use ZipArchive;

/**
 * Builds zip backups on the local disk: a database snapshot (SQLite via
 * VACUUM INTO, MySQL via a plain SQL dump), uploaded files and logs, as the
 * Backup Settings row asks. Old archives are pruned by the retention period.
 */
class BackupService
{
    public const DISK = 'local';

    public const DIRECTORY = 'backups';

    public function run(BackupRun $run, ?BackupSetting $settings = null): BackupRun
    {
        $settings ??= BackupSetting::current();
        $startedAt = microtime(true);
        $workDir = storage_path('framework/backup-'.Str::random(12));
        $fileName = 'backup-'.now()->format('Ymd-His').'-'.$run->id.'.zip';
        $zipPath = $this->disk()->path(self::DIRECTORY.'/'.$fileName);

        $run->update(['status' => 'running', 'started_at' => now(), 'error' => null]);

        try {
            File::ensureDirectoryExists(dirname($zipPath));
            File::ensureDirectoryExists($workDir);

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Could not create the backup archive.');
            }

            $contents = [];

            if ($settings->backup_database) {
                $dump = $this->dumpDatabase($workDir);
                $zip->addFile($dump, 'database/'.basename($dump));
                $contents[] = 'database';
            }

            if ($settings->backup_files) {
                $this->addDirectory($zip, storage_path('app/public'), 'files/public');
                $this->addDirectory($zip, storage_path('app/private'), 'files/private', [$this->disk()->path(self::DIRECTORY)]);
                $contents[] = 'files';
            }

            if ($settings->backup_logs) {
                $this->addDirectory($zip, storage_path('logs'), 'logs');
                $contents[] = 'logs';
            }

            if ($contents === []) {
                $zip->close();
                throw new RuntimeException('Nothing is selected to back up.');
            }

            if (! $zip->close()) {
                throw new RuntimeException('Could not finish writing the backup archive.');
            }

            $run->update([
                'status' => 'completed',
                'file_name' => $fileName,
                'size_bytes' => filesize($zipPath) ?: 0,
                'contents' => $contents,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            File::delete($zipPath);
            report($e);

            $run->update([
                'status' => 'failed',
                'error' => Str::limit($e->getMessage(), 1000),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'completed_at' => now(),
            ]);
        } finally {
            File::deleteDirectory($workDir);
        }

        $this->prune($settings->retentionDays());

        if ($settings->email_notification) {
            $this->notify($run);
        }

        return $run;
    }

    /**
     * Delete archives (and their history rows) older than the retention period.
     */
    public function prune(int $retentionDays): int
    {
        $expired = BackupRun::query()->where('created_at', '<', now()->subDays($retentionDays))->get();

        foreach ($expired as $run) {
            $this->deleteArchive($run);
        }

        return $expired->count();
    }

    public function deleteArchive(BackupRun $run): void
    {
        if ($run->file_name) {
            $this->disk()->delete(self::DIRECTORY.'/'.$run->file_name);
        }

        $run->delete();
    }

    public function archivePath(BackupRun $run): ?string
    {
        if (! $run->isDownloadable() || ! $this->disk()->exists(self::DIRECTORY.'/'.$run->file_name)) {
            return null;
        }

        return self::DIRECTORY.'/'.$run->file_name;
    }

    public function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    /**
     * Write a consistent copy of the default database into the work directory.
     */
    private function dumpDatabase(string $workDir): string
    {
        $connection = DB::connection();

        return match ($connection->getDriverName()) {
            'sqlite' => $this->dumpSqlite($connection, $workDir.'/database.sqlite'),
            'mysql', 'mariadb' => $this->dumpMysql($connection, $workDir.'/database.sql'),
            default => throw new RuntimeException('Backups support SQLite and MySQL databases only.'),
        };
    }

    private function dumpSqlite(Connection $connection, string $path): string
    {
        $connection->statement('VACUUM INTO '.$connection->getPdo()->quote($path));

        return $path;
    }

    private function dumpMysql(Connection $connection, string $path): string
    {
        $pdo = $connection->getPdo();
        $handle = fopen($path, 'wb');

        fwrite($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'") as $row) {
            $table = (string) array_values((array) $row)[0];
            $create = array_values((array) $connection->selectOne('SHOW CREATE TABLE `'.$table.'`'))[1];

            fwrite($handle, 'DROP TABLE IF EXISTS `'.$table."`;\n".$create.";\n\n");

            foreach ($connection->table($table)->cursor() as $record) {
                $values = array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), (array) $record);
                fwrite($handle, 'INSERT INTO `'.$table.'` VALUES ('.implode(',', $values).");\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return $path;
    }

    /**
     * @param  array<int, string>  $exclude  absolute paths skipped with everything beneath them
     */
    private function addDirectory(ZipArchive $zip, string $directory, string $prefix, array $exclude = []): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $exclude = array_map(fn (string $path) => rtrim(str_replace('\\', '/', $path), '/').'/', $exclude);
        $root = rtrim(str_replace('\\', '/', $directory), '/').'/';

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            if (! $file->isFile() || $file->getFilename() === '.gitignore') {
                continue;
            }

            foreach ($exclude as $skip) {
                if (str_starts_with($path, $skip)) {
                    continue 2;
                }
            }

            $zip->addFile($file->getPathname(), $prefix.'/'.substr($path, strlen($root)));
        }
    }

    private function notify(BackupRun $run): void
    {
        $admins = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'super_admin'))
            ->where('status', 'active')
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new BackupCompleted($run));
        }
    }
}
