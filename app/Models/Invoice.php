<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'society_id', 'subscription_id', 'member_name', 'flat_number', 'building_name',
        'invoice_type', 'category', 'invoice_date', 'due_date', 'amount', 'tax_amount', 'total_amount',
        'paid_amount', 'outstanding_amount', 'status', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'partially_paid', 'overdue'], true) && (float) $this->outstanding_amount > 0;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'badge-success',
            'partially_paid' => 'badge-info',
            'pending' => 'badge-warning',
            'overdue' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
