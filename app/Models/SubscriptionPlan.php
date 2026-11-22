<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Modules a plan can grant access to, keyed by module_key.
     *
     * @var array<string, array{name: string, description: string, icon: string, color: string}>
     */
    public const MODULES = [
        'dashboard' => ['name' => 'Dashboard', 'description' => 'View dashboard & analytics', 'icon' => 'fa-chart-line', 'color' => 'info'],
        'user_management' => ['name' => 'User Management', 'description' => 'Manage members & users', 'icon' => 'fa-users', 'color' => 'success'],
        'visitor_management' => ['name' => 'Visitor Management', 'description' => 'Manage visitors & gate entries', 'icon' => 'fa-walking', 'color' => 'purple'],
        'complaint_management' => ['name' => 'Complaint Management', 'description' => 'Manage complaints & tickets', 'icon' => 'fa-exclamation-circle', 'color' => 'orange'],
        'facility_management' => ['name' => 'Facility Management', 'description' => 'Manage society facilities & amenities', 'icon' => 'fa-couch', 'color' => 'teal'],
        'revenue_&_billing' => ['name' => 'Revenue & Billing', 'description' => 'Manage invoices & payments', 'icon' => 'fa-rupee-sign', 'color' => 'warning'],
        'reports' => ['name' => 'Reports', 'description' => 'View & export reports', 'icon' => 'fa-chart-bar', 'color' => 'pink'],
        'notifications' => ['name' => 'Notifications', 'description' => 'Send & manage notifications', 'icon' => 'fa-bell', 'color' => 'info'],
        'activity_logs' => ['name' => 'Activity Logs', 'description' => 'View activity & audit logs', 'icon' => 'fa-clipboard-list', 'color' => 'success'],
        'document_management' => ['name' => 'Document Management', 'description' => 'Manage documents & files', 'icon' => 'fa-file-alt', 'color' => 'purple'],
    ];

    /**
     * Months covered by one payment, keyed by billing_cycle.
     *
     * @var array<string, int>
     */
    public const CYCLE_MONTHS = ['monthly' => 1, 'quarterly' => 3, 'half_yearly' => 6, 'yearly' => 12];

    /**
     * Unit limits at or above this are shown as "any number of units".
     */
    public const UNLIMITED_UNITS = 9999;

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

    /**
     * Replace the plan's module rows so exactly the given keys are enabled.
     *
     * @param  array<int, string>  $enabledKeys
     */
    public function syncModules(array $enabledKeys): void
    {
        $this->modules()->delete();

        foreach (self::MODULES as $key => $module) {
            $this->modules()->create([
                'module_name' => $module['name'],
                'module_key' => $key,
                'description' => $module['description'],
                'is_enabled' => in_array($key, $enabledKeys, true),
            ]);
        }
    }

    /**
     * Plans without a list price are quoted per society.
     */
    public function isCustomPriced(): bool
    {
        return (float) $this->amount <= 0;
    }

    public function hasUnlimitedUnits(): bool
    {
        return (int) $this->max_units >= self::UNLIMITED_UNITS;
    }

    public function cycleSuffix(): string
    {
        return match ($this->billing_cycle) {
            'monthly' => 'month',
            'quarterly' => 'quarter',
            'half_yearly' => 'half-year',
            default => 'year',
        };
    }

    /**
     * Cost per unit per month when the plan is filled to its unit limit.
     */
    public function monthlyCostPerUnit(): ?float
    {
        if ($this->isCustomPriced() || $this->hasUnlimitedUnits() || (int) $this->max_units < 1) {
            return null;
        }

        return (float) $this->amount / (self::CYCLE_MONTHS[$this->billing_cycle] ?? 12) / (int) $this->max_units;
    }

    /**
     * @return array<int, string>
     */
    public function enabledModuleKeys(): array
    {
        return $this->modules->where('is_enabled', true)->pluck('module_key')->values()->all();
    }
}
