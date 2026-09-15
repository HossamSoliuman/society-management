<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayOrder extends Model
{
    use BelongsToSociety;

    protected $fillable = [
        'society_id', 'member_id', 'unit_id', 'maintenance_bill_id', 'collection_payment_id', 'created_by',
        'provider', 'provider_order_id', 'provider_payment_id', 'amount', 'currency', 'status',
        'payment_method', 'payload', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

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

    public function bill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class, 'maintenance_bill_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(CollectionPayment::class, 'collection_payment_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
