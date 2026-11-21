<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory;

    /** Idle time before a signed-in session expires, in minutes. */
    public const AUTO_LOGOUT_MINUTES = [
        '15_minutes' => 15,
        '30_minutes' => 30,
        '1_hour' => 60,
        '2_hours' => 120,
    ];

    /** How long a login stays locked after too many failed attempts, in minutes. */
    public const LOCK_MINUTES = [
        '15_minutes' => 15,
        '30_minutes' => 30,
        '1_hour' => 60,
        '24_hours' => 1440,
    ];

    protected $fillable = [
        'min_password_length', 'password_expiry', 'require_uppercase',
        'require_lowercase', 'require_number', 'require_special',
        'enable_2fa', '2fa_for_super_admin', '2fa_for_others',
        'auto_logout', 'login_attempts_limit', 'account_lock_duration',
    ];

    /**
     * Platform security row; falls back to defaults on a fresh install.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'auto_logout' => '2_hours',
            'login_attempts_limit' => 5,
            'account_lock_duration' => '15_minutes',
        ]);
    }

    public function sessionLifetimeMinutes(): int
    {
        return self::AUTO_LOGOUT_MINUTES[$this->auto_logout] ?? 120;
    }

    public function loginAttemptsLimit(): int
    {
        return max(1, (int) ($this->login_attempts_limit ?: 5));
    }

    public function lockSeconds(): int
    {
        return (self::LOCK_MINUTES[$this->account_lock_duration] ?? 15) * 60;
    }
}
