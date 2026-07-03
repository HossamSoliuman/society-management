<?php

namespace App\Models;

use Database\Factories\TenderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tender extends Model
{
    /** @use HasFactory<TenderFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'title', 'sub_title', 'reference_no', 'department',
        'tender_type', 'description', 'tender_category', 'estimated_value',
        'emd_amount', 'start_date', 'end_date', 'submission_deadline',
        'opening_date', 'validity_days', 'payment_terms', 'delivery_terms',
        'contract_type', 'tax_option', 'terms_conditions', 'eligibility_criteria',
        'evaluation_criteria', 'contact_person', 'contact_email', 'contact_phone',
        'venue', 'visibility', 'allow_online_submission', 'allow_partial_bidding',
        'notes', 'status', 'awarded_vendor', 'contract_value', 'awarded_date',
        'closed_date', 'closed_reason', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submission_deadline' => 'datetime',
            'opening_date' => 'datetime',
            'awarded_date' => 'date',
            'closed_date' => 'date',
            'estimated_value' => 'decimal:2',
            'emd_amount' => 'decimal:2',
            'contract_value' => 'decimal:2',
            'allow_online_submission' => 'boolean',
            'allow_partial_bidding' => 'boolean',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function typeBadgeClass(): string
    {
        return $this->tender_type === 'supply' ? 'badge-info' : 'badge-info';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'open' => 'badge-success',
            'in_progress', 'under_review' => 'badge-warning',
            'awarded' => 'badge-success',
            'closed' => 'badge-secondary',
            default => 'badge-warning',
        };
    }
}
