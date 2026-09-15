<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use BelongsToSociety, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'user_id', 'name', 'member_type', 'flat_unit', 'tower_wing',
        'mobile', 'email', 'status', 'avatar', 'join_date', 'invited_at',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'invited_at' => 'datetime',
        ];
    }

    /**
     * Portal login linked to this member (null until invited).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function hasPortalAccess(): bool
    {
        return $this->user_id !== null;
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function bills()
    {
        return $this->hasMany(MaintenanceBill::class);
    }

    public function payments()
    {
        return $this->hasMany(CollectionPayment::class);
    }

    /**
     * Map the member status to a `.status-badge` state class.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'active',
            'inactive' => 'expiring_soon',
            'blocked' => 'overdue',
            default => 'cancelled',
        };
    }

    /**
     * Title-case label for the member type enum.
     */
    public function typeLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->member_type));
    }
}
