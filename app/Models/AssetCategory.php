<?php

namespace App\Models;

use Database\Factories\AssetCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    /** @use HasFactory<AssetCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'name', 'code', 'icon', 'color', 'description',
        'status', 'display_order', 'movable', 'immovable', 'parent_id',
        'asset_life_years', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'movable' => 'boolean',
            'immovable' => 'boolean',
            'asset_life_years' => 'integer',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The light-background + solid-foreground CSS variables for the given tint name.
     *
     * @return array{0: string, 1: string} [background, color]
     */
    public static function tint(string $color): array
    {
        return [
            'primary' => ['var(--primary-light)', 'var(--primary)'],
            'blue' => ['var(--info-light)', 'var(--info)'],
            'green' => ['var(--success-light)', 'var(--success)'],
            'red' => ['var(--danger-light)', 'var(--danger)'],
            'orange' => ['var(--orange-light)', 'var(--orange)'],
            'purple' => ['var(--purple-light)', 'var(--purple)'],
            'teal' => ['var(--teal-light)', 'var(--teal)'],
            'pink' => ['var(--pink-light)', 'var(--pink)'],
            'yellow' => ['var(--warning-light)', 'var(--warning)'],
            'gray' => ['var(--gray-100)', 'var(--gray-500)'],
        ][$color] ?? ['var(--gray-100)', 'var(--gray-500)'];
    }

    public function statusBadgeClass(): string
    {
        return $this->status === 'active' ? 'badge-success' : 'badge-danger';
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }
}
