<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscription_number', 'society_id', 'plan_id', 'building_name',
        'monthly_cost_per_flat', 'start_date', 'end_date', 'additional_free_days',
        'billing_cycle', 'description', 'status', 'payment_method', 'payment_date',
        'payment_proof', 'reference_number', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
    ];

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}
