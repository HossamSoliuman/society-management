<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'society_id', 'member_name', 'flat_number', 'building_name',
        'invoice_type', 'invoice_date', 'due_date', 'amount', 'tax_amount', 'total_amount',
        'paid_amount', 'outstanding_amount', 'status', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
