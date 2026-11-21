<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One backup attempt (scheduled or manual) and the archive it produced.
 */
class BackupRun extends Model
{
    protected $fillable = [
        'trigger', 'status', 'file_name', 'size_bytes', 'contents', 'duration_ms',
        'error', 'triggered_by', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'contents' => 'array',
            'size_bytes' => 'integer',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function isDownloadable(): bool
    {
        return $this->status === 'completed' && $this->file_name !== null;
    }

    public function sizeLabel(): string
    {
        $bytes = (int) $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        return round($bytes / (1024 ** $power), $power === 0 ? 0 : 1).' '.$units[$power];
    }

    public function durationLabel(): string
    {
        if ($this->duration_ms === null) {
            return '—';
        }

        return $this->duration_ms < 1000 ? $this->duration_ms.'ms' : round($this->duration_ms / 1000, 1).'s';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completed' => 'completed',
            'failed' => 'failed',
            default => 'pending',
        };
    }
}
