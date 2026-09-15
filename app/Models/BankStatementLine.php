<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementLine extends Model
{
    use BelongsToSociety;

    protected $fillable = [
        'society_id', 'account_id', 'import_batch', 'statement_date', 'description', 'reference',
        'debit', 'credit', 'balance', 'transaction_id', 'matched_at',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
        'matched_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Net amount from the bank's point of view: credits increase the balance.
     */
    public function amount(): float
    {
        return round((float) $this->credit - (float) $this->debit, 2);
    }
}
