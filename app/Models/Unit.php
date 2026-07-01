<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'unit_number', 'building', 'wing', 'floor', 'unit_type',
        'area_sqft', 'status', 'occupied_by_name', 'occupied_by_role',
        'owner_name', 'owner_mobile',
    ];

    protected function casts(): array
    {
        return [
            'area_sqft' => 'integer',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Map the unit status to a `.status-badge` state class.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'occupied' => 'active',
            'vacant' => 'expiring_soon',
            'under_maintenance' => 'purple',
            default => 'cancelled',
        };
    }

    /**
     * Human readable status label.
     */
    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
