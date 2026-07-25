<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\AmcCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmcCategory extends Model
{
    /** @use HasFactory<AmcCategoryFactory> */
    use BelongsToSociety, HasFactory;

    protected $fillable = [
        'society_id', 'name', 'description', 'icon', 'assets_covered',
        'applicable_assets', 'default_reminder_days', 'default_duration_months',
        'tax_applicable', 'notes', 'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applicable_assets' => 'array',
            'tax_applicable' => 'boolean',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(AmcContract::class);
    }

    public function statusBadgeClass(): string
    {
        return $this->status === 'active' ? 'badge-success' : 'badge-danger';
    }
}
