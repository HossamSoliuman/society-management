<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmtpSetting;

class SmtpSettingSeeder extends Seeder
{
    public function run(): void
    {
        SmtpSetting::create([
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'encryption' => 'STARTTLS',
            'authentication' => 'Login',
            'smtp_username' => 'noreply@greenfieldresidency.com',
            'smtp_password' => 'encrypted_password_here',
            'from_email' => 'noreply@greenfieldresidency.com',
            'from_name' => 'Greenfield Residency',
            'reply_to_email' => 'support@greenfieldresidency.com',
            'enable_ssl_tls' => true,
            'enable_email_logging' => true,
            'send_test_email' => false,
        ]);
    }
}
