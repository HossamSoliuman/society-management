<?php

namespace App\Models;

use Database\Factories\DemoRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A "request a demo" lead submitted from the public marketing site.
 */
class DemoRequest extends Model
{
    /** @use HasFactory<DemoRequestFactory> */
    use HasFactory;

    public const ROLES = [
        'secretary' => 'Secretary',
        'chairman' => 'Chairman / President',
        'treasurer' => 'Treasurer',
        'committee_member' => 'Committee member',
        'facility_manager' => 'Facility / estate manager',
        'resident' => 'Resident',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'new' => ['New', 'badge-warning'],
        'contacted' => ['Contacted', 'badge-info'],
        'converted' => ['Converted', 'badge-success'],
        'closed' => ['Closed', 'badge-secondary'],
    ];

    protected $fillable = [
        'name', 'society_name', 'city', 'units', 'role', 'phone', 'email', 'message',
        'status', 'admin_note', 'handled_by', 'handled_at', 'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'units' => 'integer',
            'handled_at' => 'datetime',
        ];
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? ucfirst($this->status);
    }

    public function statusBadgeClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'badge-secondary';
    }
}
