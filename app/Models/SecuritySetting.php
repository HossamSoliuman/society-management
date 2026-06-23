<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_password_length', 'password_expiry', 'require_uppercase',
        'require_lowercase', 'require_number', 'require_special',
        'enable_2fa', '2fa_for_super_admin', '2fa_for_others',
        'auto_logout', 'login_attempts_limit', 'account_lock_duration',
    ];
}
