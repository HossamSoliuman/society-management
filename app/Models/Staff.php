<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use App\Models\Concerns\LogsActivity;
use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use BelongsToSociety, LogsActivity, SoftDeletes;

    /** @use HasFactory<StaffFactory> */
    use HasFactory;

    protected $table = 'staff';

    public const ROLES = [
        'Security Guard', 'Security Supervisor', 'Housekeeping', 'Gardener', 'Electrician', 'Plumber',
        'Lift Operator', 'Facility Manager', 'Office Assistant', 'Driver', 'Other',
    ];

    public const SHIFTS = ['general' => 'General (9–6)', 'morning' => 'Morning', 'evening' => 'Evening', 'night' => 'Night'];

    public const EMPLOYMENT_TYPES = ['society' => 'Society payroll', 'agency' => 'Agency / contractor'];

    public const STATUSES = ['active' => 'Active', 'inactive' => 'Inactive', 'left' => 'Left'];

    protected $fillable = [
        'society_id', 'vendor_id', 'employee_code', 'name', 'role', 'employment_type', 'shift', 'mobile',
        'email', 'emergency_contact', 'address', 'id_proof_type', 'id_proof_number', 'joining_date',
        'monthly_salary', 'status', 'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'monthly_salary' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ServiceVendor::class, 'vendor_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function shiftLabel(): string
    {
        return self::SHIFTS[$this->shift] ?? ucfirst($this->shift);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'inactive' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    protected function activityModule(): string
    {
        return 'Staff';
    }
}
