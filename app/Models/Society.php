<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Society extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'registration_number', 'prefix', 'society_type_id', 'registration_date',
        'pan_number', 'flats_count', 'shops_count', 'offices_count',
        'address_line_1', 'address_line_2', 'city', 'state', 'pincode',
        'primary_email', 'secondary_email', 'primary_mobile', 'alternate_mobile',
        'landline', 'website',
        'chairman_name', 'chairman_mobile', 'chairman_email',
        'secretary_name', 'secretary_mobile', 'secretary_email',
        'treasurer_name', 'treasurer_mobile', 'treasurer_email',
        'subscription_plan_id', 'subscription_start_date', 'subscription_end_date',
        'billing_cycle', 'grace_period_days', 'auto_renewal', 'trial_period_days',
        'subscription_status', 'status', 'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
        'auto_renewal' => 'boolean',
    ];

    public function societyType()
    {
        return $this->belongsTo(SocietyType::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function getTotalUnitsAttribute()
    {
        return $this->flats_count + $this->shops_count + $this->offices_count;
    }
}
