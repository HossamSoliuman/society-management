<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanModule extends Model
{
    use HasFactory;

    protected $fillable = ['plan_id', 'module_name', 'module_key', 'description', 'is_enabled'];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}
