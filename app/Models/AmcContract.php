<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\AmcContractFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AmcContract extends Model
{
    /** @use HasFactory<AmcContractFactory> */
    use BelongsToSociety, HasFactory;

    protected $fillable = [
        'society_id', 'item_asset', 'item_sub', 'amc_category_id',
        'vendor_name', 'service_vendor_id', 'contract_no', 'po_invoice_no',
        'contract_type', 'start_date', 'end_date', 'duration_months', 'amount',
        'tax_percent', 'renewal_reminder_days', 'description', 'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'amount' => 'decimal:2',
            'tax_percent' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AmcCategory::class, 'amc_category_id');
    }

    /** Whole days from now until the contract end date (negative when expired). */
    public function daysLeft(): int
    {
        if (! $this->end_date) {
            return 0;
        }

        return (int) round(Carbon::now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false));
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'expiring_soon' => 'badge-warning',
            'expired' => 'badge-danger',
            default => 'badge-info',
        };
    }
}
