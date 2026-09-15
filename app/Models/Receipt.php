<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use App\Observers\LedgerPostingObserver;
use Database\Factories\ReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(LedgerPostingObserver::class)]
class Receipt extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<ReceiptFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'receipt_no', 'date', 'payer_name', 'flat_no',
        'receipt_type', 'reference_no', 'mode_of_payment', 'amount',
        'account_id', 'income_account_id', 'location', 'status',
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

    public function incomeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'income_account_id');
    }

    public function typeBadgeClass(): string
    {
        return match ($this->receipt_type) {
            'maintenance' => 'badge-success',
            'other_charges' => 'badge-info',
            'amenities' => 'badge-warning',
            'interest_penalty' => 'badge-purple',
            default => 'badge-secondary',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->receipt_type) {
            'maintenance' => 'Maintenance',
            'other_charges' => 'Other Charges',
            'amenities' => 'Amenities',
            'interest_penalty' => 'Interest & Penalty',
            default => ucfirst($this->receipt_type),
        };
    }

    public function modeIcon(): string
    {
        return match ($this->mode_of_payment) {
            'UPI' => 'fa-mobile-screen',
            'Card' => 'fa-credit-card',
            'Net Banking' => 'fa-building-columns',
            'Cash' => 'fa-money-bill-wave',
            default => 'fa-wallet',
        };
    }
}
