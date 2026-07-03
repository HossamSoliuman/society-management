<?php

namespace App\Models;

use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory;

    protected $fillable = [
        'title', 'notice_type', 'priority', 'short_description', 'content',
        'attach_path', 'publish_at', 'expires_at', 'pin_to_dashboard',
        'audience_type', 'estimated_recipients', 'send_email', 'send_sms',
        'require_acknowledgement', 'status', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
            'pin_to_dashboard' => 'boolean',
            'send_email' => 'boolean',
            'send_sms' => 'boolean',
            'require_acknowledgement' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'high' => 'badge-danger',
            'medium' => 'badge-warning',
            default => 'badge-success',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'published' => 'badge-success',
            'scheduled' => 'badge-info',
            'expired' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
