<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number', 'invoice_id', 'society_id', 'member_name', 'flat_number',
        'amount', 'payment_method', 'transaction_id', 'payment_date', 'status', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }
}
