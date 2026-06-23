<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use App\Models\SmtpSetting;
use App\Models\BackupSetting;
use App\Models\SecuritySetting;
use App\Models\PrefixSetting;

class SettingController extends Controller
{
    public function companyProfile()
    {
        $company = CompanyProfile::first();
        return view('superadmin.settings.company-profile', compact('company'));
    }

    public function updateCompanyProfile(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'required|string',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
        ]);

        CompanyProfile::first()->update($validated);
        return redirect()->back()->with('success', 'Company profile updated');
    }

    public function prefixSettings()
    {
        $prefixes = PrefixSetting::latest()->paginate(10);
        return view('superadmin.settings.prefix', compact('prefixes'));
    }

    public function storePrefix(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:100',
            'prefix' => 'required|string|max:20',
            'starting_number' => 'required|integer|min:1',
            'padding' => 'required|integer|min:1',
        ]);

        $validated['current_number'] = $validated['starting_number'];
        $validated['status'] = 'active';

        PrefixSetting::create($validated);
        return redirect()->back()->with('success', 'Prefix setting created');
    }

    public function smtpSettings()
    {
        $smtp = SmtpSetting::first();
        return view('superadmin.settings.smtp', compact('smtp'));
    }

    public function updateSmtp(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer',
            'encryption' => 'required|string|max:50',
            'authentication' => 'required|string|max:50',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'required|string|max:255',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'enable_ssl_tls' => 'boolean',
            'enable_email_logging' => 'boolean',
        ]);

        $validated['enable_ssl_tls'] = $request->boolean('enable_ssl_tls');
        $validated['enable_email_logging'] = $request->boolean('enable_email_logging');

        SmtpSetting::first()->update($validated);
        return redirect()->back()->with('success', 'SMTP settings updated');
    }

    public function backupSettings()
    {
        $backup = BackupSetting::first();
        return view('superadmin.settings.backup', compact('backup'));
    }

    public function updateBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_time' => 'required',
            'retention_period' => 'required|in:7_days,14_days,30_days,60_days,90_days',
            'backup_location' => 'required|in:local,aws_s3,google_drive',
            'email_notification' => 'boolean',
            'backup_database' => 'boolean',
            'backup_user_data' => 'boolean',
            'backup_files' => 'boolean',
            'backup_logs' => 'boolean',
            'backup_settings' => 'boolean',
        ]);

        foreach (['email_notification', 'backup_database', 'backup_user_data', 'backup_files', 'backup_logs', 'backup_settings'] as $field) {
            $validated[$field] = $request->boolean($field);
        }

        BackupSetting::first()->update($validated);
        return redirect()->back()->with('success', 'Backup settings updated');
    }

    public function securitySettings()
    {
        $security = SecuritySetting::first();
        return view('superadmin.settings.security', compact('security'));
    }

    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'min_password_length' => 'required|integer|min:4|max:32',
            'password_expiry' => 'required|in:30_days,60_days,90_days,180_days,never',
            'require_uppercase' => 'boolean',
            'require_lowercase' => 'boolean',
            'require_number' => 'boolean',
            'require_special' => 'boolean',
            'enable_2fa' => 'boolean',
            '2fa_for_super_admin' => 'required|in:required,optional',
            '2fa_for_others' => 'required|in:required,optional',
            'auto_logout' => 'required|in:15_minutes,30_minutes,1_hour,2_hours',
            'login_attempts_limit' => 'required|integer|min:1|max:10',
            'account_lock_duration' => 'required|in:15_minutes,30_minutes,1_hour,24_hours',
        ]);

        foreach (['require_uppercase', 'require_lowercase', 'require_number', 'require_special', 'enable_2fa'] as $field) {
            $validated[$field] = $request->boolean($field);
        }

        SecuritySetting::first()->update($validated);
        return redirect()->back()->with('success', 'Security settings updated');
    }
}
