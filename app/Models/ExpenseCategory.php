<?php

namespace App\Models;

use Database\Factories\ExpenseCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    /** @use HasFactory<ExpenseCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'name', 'slug', 'icon', 'color', 'description',
        'status', 'display_order', 'applicable_for', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'category_id');
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

    /**
     * The `.badge-*` class used when this category appears as a pill in the expenses table.
     */
    public function badgeClass(): string
    {
        return match (strtolower($this->name)) {
            'utilities' => 'badge-info',
            'salary' => 'badge-success',
            'maintenance', 'lift maintenance', 'garden maintenance', 'security' => 'badge-orange',
            'services', 'pest control' => 'badge-purple',
            'repairs' => 'badge-warning',
            'purchase' => 'badge-teal',
            default => match ($this->color) {
                'blue' => 'badge-info',
                'green' => 'badge-success',
                'orange' => 'badge-orange',
                'purple' => 'badge-purple',
                'teal' => 'badge-teal',
                'yellow' => 'badge-warning',
                'red' => 'badge-danger',
                'primary' => 'badge-primary',
                default => 'badge-secondary',
            },
        };
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
