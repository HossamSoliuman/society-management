<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'receipt_number', 'member_id', 'unit_id', 'maintenance_bill_id',
        'member_name', 'member_mobile', 'member_email', 'flat_number', 'unit_label', 'member_code', 'unit_type',
        'bill_type', 'bill_period', 'due_date', 'receipt_date',
        'total_due', 'paid_amount', 'discount', 'fine_penalty', 'balance_due',
        'payment_mode', 'reference_no', 'transaction_utr', 'collected_by',
        'status', 'is_online', 'notes', 'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'receipt_date' => 'datetime',
            'total_due' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'fine_penalty' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'is_online' => 'boolean',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function maintenanceBill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class);
    }

    /**
     * Map the payment status to a `.status-badge` state class (per design §3.4).
     * Paid = green, Partial = amber, Pending = red-pink, Overdue = red, Refunded = purple.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'paid',
            'partial' => 'expiring_soon',
            'pending' => 'overdue',
            'overdue' => 'overdue',
            'refunded' => 'refunded',
            default => 'cancelled',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Human label for the payment mode enum (Cash, UPI, Card, Net Banking, Cheque, Other).
     */
    public function paymentModeLabel(): string
    {
        return match ($this->payment_mode) {
            'cash' => 'Cash',
            'upi' => 'UPI',
            'card' => 'Card',
            'net_banking' => 'Net Banking',
            'cheque' => 'Cheque',
            'other' => 'Other',
            default => '',
        };
    }

    /**
     * Font Awesome icon representing the payment mode (used for the small brand icon).
     */
    public function paymentModeIcon(): string
    {
        return match ($this->payment_mode) {
            'cash' => 'fa-money-bill-wave',
            'upi' => 'fa-mobile-screen',
            'card' => 'fa-credit-card',
            'net_banking' => 'fa-building-columns',
            'cheque' => 'fa-money-check',
            default => 'fa-ellipsis',
        };
    }

    /**
     * Indian-system amount in words for the paid amount (uses the 2A helper).
     */
    public function amountInWords(): string
    {
        return amount_in_words_inr($this->paid_amount);
    }
}
