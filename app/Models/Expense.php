<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'code', 'expense_date', 'title', 'category_id', 'vendor_id',
        'reference_no', 'payment_mode', 'amount', 'tax_amount', 'paid_amount', 'due_amount',
        'bill_date', 'payment_status', 'expense_for', 'description',
        'attachment_path', 'attachment_notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'bill_date' => 'date',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** Map the payment status to a `.status-badge` state class. */
    public function statusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'paid',
            'pending' => 'pending',
            'overdue' => 'overdue',
            'cancelled' => 'cancelled',
            default => 'cancelled',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst($this->payment_status);
    }

    /** Font Awesome icon for the payment-mode pill. */
    public function paymentModeIcon(): string
    {
        return match ($this->payment_mode) {
            'Net Banking' => 'fa-building-columns',
            'Cash' => 'fa-money-bill-wave',
            'Cheque' => 'fa-money-check',
            'UPI' => 'fa-mobile-screen',
            'Card' => 'fa-credit-card',
            'Bank Transfer' => 'fa-right-left',
            default => 'fa-wallet',
        };
    }
}
