<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceBillItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'maintenance_bill_id', 'charge_head_id', 'charge_head_name',
        'description', 'amount', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class, 'maintenance_bill_id');
    }

    public function chargeHead(): BelongsTo
    {
        return $this->belongsTo(ChargeHead::class);
    }
}
