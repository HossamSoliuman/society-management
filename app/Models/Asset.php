<?php

namespace App\Models;

use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'name', 'asset_code', 'category_id', 'brand', 'model',
        'serial_number', 'description', 'location', 'tower_wing', 'floor',
        'area_room', 'assigned_to', 'vendor_supplier', 'purchase_from',
        'purchase_date', 'purchase_cost', 'warranty_start', 'warranty_end',
        'invoice_no', 'invoice_date', 'expected_life_years', 'depreciation_method',
        'status', 'condition', 'usage_type', 'qr_code', 'notes', 'current_value',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_start' => 'date',
            'warranty_end' => 'date',
            'invoice_date' => 'date',
            'purchase_cost' => 'decimal:2',
            'current_value' => 'decimal:2',
            'expected_life_years' => 'integer',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AssetImage::class);
    }

    public function primaryImage(): ?AssetImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    /** Map the asset status to a `.status-badge` state class. */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'in_use' => 'active',
            'under_maintenance' => 'pending',
            'inactive', 'disposed' => 'inactive',
            default => 'cancelled',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'in_use' => 'In Use',
            'under_maintenance' => 'Under Maintenance',
            'inactive' => 'Inactive',
            'disposed' => 'Disposed',
            default => ucfirst($this->status),
        };
    }
}
