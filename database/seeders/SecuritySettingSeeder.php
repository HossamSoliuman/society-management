<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecuritySetting;

class SecuritySettingSeeder extends Seeder
{
    public function run(): void
    {
        SecuritySetting::create([
            'min_password_length' => 8,
            'password_expiry' => '90_days',
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_number' => true,
            'require_special' => true,
            'enable_2fa' => true,
            '2fa_for_super_admin' => 'required',
            '2fa_for_others' => 'optional',
            'auto_logout' => '30_minutes',
            'login_attempts_limit' => 5,
            'account_lock_duration' => '30_minutes',
        ]);
    }
}
