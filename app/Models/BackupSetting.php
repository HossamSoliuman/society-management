<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_frequency', 'backup_time', 'retention_period', 'backup_location',
        'email_notification', 'backup_database', 'backup_user_data',
        'backup_files', 'backup_logs', 'backup_settings',
    ];

    protected function casts(): array
    {
        return [
            'email_notification' => 'boolean',
            'backup_database' => 'boolean',
            'backup_user_data' => 'boolean',
            'backup_files' => 'boolean',
            'backup_logs' => 'boolean',
            'backup_settings' => 'boolean',
        ];
    }

    /**
     * Row the backup job runs with; falls back to defaults on a fresh install.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'backup_frequency' => 'daily',
            'backup_time' => '02:00',
            'retention_period' => '30_days',
            'backup_location' => 'local',
            'email_notification' => true,
            'backup_database' => true,
            'backup_files' => true,
            'backup_logs' => false,
        ]);
    }

    public function retentionDays(): int
    {
        return max(1, (int) $this->retention_period);
    }

    /**
     * Scheduler time in "HH:MM" form (the column stores HH:MM:SS).
     */
    public function scheduleTime(): string
    {
        return substr((string) ($this->backup_time ?: '02:00'), 0, 5);
    }
}
