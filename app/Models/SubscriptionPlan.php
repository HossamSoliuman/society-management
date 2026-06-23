<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'plan_type', 'description', 'amount', 'max_units',
        'billing_cycle', 'plan_duration', 'trial_period_days', 'badge',
        'color', 'priority', 'status',
    ];

    public function modules()
    {
        return $this->hasMany(PlanModule::class, 'plan_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function societies()
    {
        return $this->hasMany(Society::class);
    }
}
