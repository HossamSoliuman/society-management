<?php

namespace App\Models;

use Database\Factories\SupportRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportRequest extends Model
{
    /** @use HasFactory<SupportRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'request_id', 'subject', 'category', 'raised_by_type',
        'member_id', 'raised_by_name', 'flat_no', 'mobile', 'email',
        'preferred_contact', 'priority', 'status', 'description', 'location',
        'attachment_path', 'notes', 'raised_at',
    ];

    protected function casts(): array
    {
        return [
            'raised_at' => 'datetime',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /** The `.badge-*` class for the request category pill. */
    public function categoryBadgeClass(): string
    {
        return match ($this->category) {
            'Maintenance' => 'badge-orange',
            'Lift' => 'badge-purple',
            'Electrical', 'Garden' => 'badge-success',
            'Housekeeping' => 'badge-pink',
            'Security', 'Access Control' => 'badge-teal',
            default => 'badge-secondary',
        };
    }

    /** The `.badge-*` class for the priority pill. */
    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'high' => 'badge-danger',
            'medium' => 'badge-warning',
            'low' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    public function priorityLabel(): string
    {
        return ucfirst($this->priority);
    }

    /** Map the status to a `.status-badge` state class. */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'open' => 'pending',
            'in_progress' => 'partial',
            'resolved' => 'success',
            'closed' => 'cancelled',
            default => 'cancelled',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            default => ucfirst($this->status),
        };
    }
}
