<?php

namespace Database\Seeders;

use App\Models\BackupSetting;
use Illuminate\Database\Seeder;

class BackupSettingSeeder extends Seeder
{
    public function run(): void
    {
        BackupSetting::create([
            'backup_frequency' => 'daily',
            'backup_time' => '02:00',
            'retention_period' => '30_days',
            'backup_location' => 'local',
            'email_notification' => true,
            'backup_database' => true,
            'backup_user_data' => true,
            'backup_files' => true,
            'backup_logs' => true,
            'backup_settings' => true,
        ]);
    }
}
