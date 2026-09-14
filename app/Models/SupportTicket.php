<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Single ticket table shared by the society panel ("Priority Support"),
 * the super-admin ticket desk, and (later) the member portal.
 */
class SupportTicket extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<SupportTicketFactory> */
    use HasFactory, SoftDeletes;

    public const RAISED_BY_TYPES = ['member', 'staff_admin'];

    public const STATUSES = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];

    protected $fillable = [
        'ticket_number', 'subject', 'description', 'category', 'priority',
        'status', 'created_by', 'assigned_to', 'society_id', 'resolved_at',
        'raised_by_type', 'member_id', 'raised_by_name', 'flat_no', 'mobile', 'email',
        'preferred_contact', 'location', 'attachment_path', 'notes', 'raised_at', 'last_reply_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'raised_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')->orderBy('id');
    }

    /**
     * Legacy alias used by the society support views.
     */
    public function getRequestIdAttribute(): string
    {
        return $this->ticket_number;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'reopened'], true);
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
            'Billing' => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /** The `.badge-*` class for the priority pill. */
    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'high', 'urgent' => 'badge-danger',
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
            'open', 'reopened' => 'pending',
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

    public function raisedByLabel(): string
    {
        return match ($this->raised_by_type) {
            'member' => 'Member',
            'society_admin' => 'Society Admin',
            default => 'Staff / Admin',
        };
    }
}
