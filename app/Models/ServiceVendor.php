<?php

namespace App\Models;

use Database\Factories\ServiceVendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceVendor extends Model
{
    /** @use HasFactory<ServiceVendorFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'vendor_code', 'name', 'company', 'category',
        'contact_person', 'designation', 'gst_number', 'pan_number',
        'phone', 'alternate_phone', 'email', 'website', 'address', 'city',
        'state', 'pin_code', 'bank_name', 'account_number', 'ifsc_code',
        'account_holder_name', 'services_provided', 'payment_terms',
        'credit_limit', 'notes', 'status', 'approval_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function statusBadgeClass(): string
    {
        return $this->status === 'active' ? 'badge-success' : 'badge-danger';
    }

    public function approvalBadgeClass(): string
    {
        return match ($this->approval_status) {
            'approved' => 'badge-success',
            'pending' => 'badge-warning',
            default => 'badge-danger',
        };
    }

    /** Two-letter initials for the vendor avatar chip. */
    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $letters = array_map(fn ($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2));

        return mb_strtoupper(implode('', $letters)) ?: 'V';
    }
}
