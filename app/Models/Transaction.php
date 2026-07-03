<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'date', 'type', 'reference_no', 'description',
        'account_id', 'payment_mode', 'debit', 'credit', 'running_balance', 'location',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'running_balance' => 'decimal:2',
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
