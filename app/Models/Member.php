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
        'society_id', 'name', 'member_type', 'flat_unit', 'tower_wing',
        'mobile', 'email', 'status', 'avatar', 'join_date',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
        ];
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
