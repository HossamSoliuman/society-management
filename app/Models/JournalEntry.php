<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<JournalEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'entry_no', 'date', 'narration',
        'total_debit', 'total_credit', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function statusBadgeClass(): string
    {
        return $this->status === 'posted' ? 'badge-success' : 'badge-warning';
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }
}
