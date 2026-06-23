<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_frequency', 'backup_time', 'retention_period', 'backup_location',
        'email_notification', 'backup_database', 'backup_user_data',
        'backup_files', 'backup_logs', 'backup_settings',
    ];
}
