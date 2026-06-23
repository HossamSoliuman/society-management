<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'smtp_host', 'smtp_port', 'encryption', 'authentication',
        'smtp_username', 'smtp_password', 'from_email', 'from_name',
        'reply_to_email', 'enable_ssl_tls', 'enable_email_logging', 'send_test_email',
    ];
}
