<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeHead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'description', 'category', 'type',
        'calculation_type', 'default_amount', 'applies_to', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Map the category to a `.badge-*` colour class (per design §7.3).
     */
    public function categoryBadgeClass(): string
    {
        return match ($this->category) {
            'maintenance' => 'badge-orange',
            'utilities' => 'badge-info',
            'parking' => 'badge-purple',
            'amenities' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    public function categoryLabel(): string
    {
        return ucfirst($this->category);
    }

    public function typeLabel(): string
    {
        return $this->type === 'one_time' ? 'One-time' : 'Recurring';
    }

    public function calculationTypeLabel(): string
    {
        return match ($this->calculation_type) {
            'per_flat' => 'Per Flat',
            'per_sqft' => 'Per Sqft',
            'per_slot' => 'Per Slot',
            'fixed' => 'Fixed',
            default => ucwords(str_replace('_', ' ', $this->calculation_type)),
        };
    }
}
