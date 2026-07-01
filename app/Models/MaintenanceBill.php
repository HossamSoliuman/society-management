<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'bill_number', 'member_id', 'unit_id',
        'member_name', 'flat_number', 'tower_wing', 'floor',
        'bill_month', 'bill_date', 'due_date', 'bill_cycle', 'billing_type',
        'sub_total', 'discount', 'late_fee', 'tax_amount', 'previous_dues',
        'total_amount', 'collected_amount', 'outstanding_amount', 'status',
        'collection_account', 'payment_mode', 'reference_no', 'notes',
        'send_email', 'send_sms', 'send_whatsapp',
    ];

    protected function casts(): array
    {
        return [
            'bill_date' => 'date',
            'due_date' => 'date',
            'sub_total' => 'decimal:2',
            'discount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'previous_dues' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'collected_amount' => 'decimal:2',
            'outstanding_amount' => 'decimal:2',
            'send_email' => 'boolean',
            'send_sms' => 'boolean',
            'send_whatsapp' => 'boolean',
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

    public function items(): HasMany
    {
        return $this->hasMany(MaintenanceBillItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Payments relation resolves once Phase 2C's `collection_payments` table exists.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(CollectionPayment::class);
    }

    /**
     * Map the bill status to a `.status-badge` state class (per design §3.4).
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'paid',
            'pending' => 'pending',
            'partial' => 'partial',
            'overdue' => 'overdue',
            default => 'cancelled',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Indian-system amount in words for the bill total (uses the 2A helper).
     */
    public function amountInWords(): string
    {
        return amount_in_words_inr($this->total_amount);
    }
}
