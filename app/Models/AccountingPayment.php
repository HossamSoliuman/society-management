<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use App\Observers\LedgerPostingObserver;
use Database\Factories\AccountingPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(LedgerPostingObserver::class)]
class AccountingPayment extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<AccountingPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'payment_no', 'date', 'payee', 'purpose', 'mode',
        'amount', 'account_id', 'expense_account_id', 'reference_no', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'amount' => 'decimal:2',
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

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function modeIcon(): string
    {
        return match ($this->mode) {
            'UPI' => 'fa-mobile-screen',
            'Card' => 'fa-credit-card',
            'Net Banking' => 'fa-building-columns',
            'Cheque' => 'fa-money-check',
            'Cash' => 'fa-money-bill-wave',
            default => 'fa-wallet',
        };
    }

    public function statusBadgeClass(): string
    {
        return $this->status === 'completed' ? 'badge-success' : 'badge-warning';
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }
}
