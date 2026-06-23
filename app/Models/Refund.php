<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'refund_number', 'payment_id', 'society_id', 'member_name', 'flat_number',
        'amount', 'refund_method', 'refund_date', 'status', 'reason',
    ];

    protected $casts = [
        'refund_date' => 'date',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }
}
