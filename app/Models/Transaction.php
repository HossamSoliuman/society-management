<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'date', 'type', 'reference_no', 'description',
        'account_id', 'payment_mode', 'debit', 'credit', 'running_balance', 'location',
        'source_type', 'source_id', 'reconciled_at', 'bank_statement_line_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'reconciled_at' => 'datetime',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            'receipt' => 'badge-success',
            'payment' => 'badge-danger',
            'journal' => 'badge-info',
            default => 'badge-secondary',
        };
    }

    public function typeLabel(): string
    {
        return ucfirst($this->type);
    }
}
