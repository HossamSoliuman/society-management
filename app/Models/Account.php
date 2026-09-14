<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'code', 'name', 'group_id', 'parent_id', 'type',
        'tree_no', 'indent', 'opening_balance', 'balance', 'status', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'display_order' => 'integer',
            'indent' => 'integer',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function typePillClass(): string
    {
        return $this->type === 'group' ? 'type-pill group' : 'type-pill detail';
    }

    public function statusBadgeClass(): string
    {
        return $this->status === 'active' ? 'badge-success' : 'badge-secondary';
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }
}
