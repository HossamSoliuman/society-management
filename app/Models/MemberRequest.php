<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use App\Models\Concerns\LogsActivity;
use Database\Factories\MemberRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A request to join the society (new owner / tenant / family) or to take
 * over a unit's ownership, submitted from the public join link.
 */
class MemberRequest extends Model
{
    use BelongsToSociety, LogsActivity;

    /** @use HasFactory<MemberRequestFactory> */
    use HasFactory;

    public const TYPES = ['join' => 'New resident', 'transfer' => 'Ownership transfer'];

    public const MEMBER_TYPES = ['owner' => 'Owner', 'tenant' => 'Tenant', 'family_member' => 'Family member'];

    public const STATUSES = [
        'pending' => ['Pending', 'badge-warning'],
        'approved' => ['Approved', 'badge-success'],
        'rejected' => ['Rejected', 'badge-danger'],
    ];

    protected $fillable = [
        'society_id', 'request_type', 'member_type', 'name', 'mobile', 'email', 'unit_id', 'unit_number',
        'move_in_date', 'document_path', 'message', 'status', 'reviewed_by', 'reviewed_at', 'review_note',
        'member_id', 'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'move_in_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->request_type] ?? ucfirst($this->request_type);
    }

    public function memberTypeLabel(): string
    {
        return self::MEMBER_TYPES[$this->member_type] ?? ucfirst($this->member_type);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? ucfirst($this->status);
    }

    public function statusBadgeClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'badge-secondary';
    }

    protected function activityModule(): string
    {
        return 'Member Request';
    }
}
