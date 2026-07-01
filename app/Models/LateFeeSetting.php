<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateFeeSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'enable_late_fee' => 'boolean',
            'grace_period_days' => 'integer',
            'late_fee_percent' => 'decimal:2',
            'late_fee_flat' => 'decimal:2',
            'max_late_fee_cap' => 'decimal:2',
            'enable_interest' => 'boolean',
            'interest_rate_annual' => 'decimal:2',
            'apply_interest_after_days' => 'integer',
            'exempt_members' => 'array',
            'exempt_charge_heads' => 'array',
            'exempt_bill_types' => 'array',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }
}
