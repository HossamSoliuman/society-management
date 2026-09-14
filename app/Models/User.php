<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, LogsActivity, Notifiable;

    protected $fillable = [
        'society_id',
        'name',
        'email',
        'password',
        'mobile',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasRole($role)
    {
        return $this->roles->where('status', 'active')->contains('name', $role);
    }

    public function hasAnyRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return $this->roles->where('status', 'active')->pluck('name')->intersect($roles)->isNotEmpty();
    }

    public function hasAnyPermission(array|string $permissions): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        return $this->roles()
            ->where('roles.status', 'active')
            ->whereHas('permissions', fn ($query) => $query->whereIn('permissions.name', $permissions))
            ->exists();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
