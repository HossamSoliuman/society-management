<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackupJob;
use App\Models\BackupRun;
use App\Models\BackupSetting;
use App\Models\CompanyProfile;
use App\Models\PrefixSetting;
use App\Models\SecuritySetting;
use App\Models\SmtpSetting;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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
            'smtp_password' => 'nullable|string|max:255',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'enable_ssl_tls' => 'boolean',
            'enable_email_logging' => 'boolean',
        ]);

        $validated['enable_ssl_tls'] = $request->boolean('enable_ssl_tls');
        $validated['enable_email_logging'] = $request->boolean('enable_email_logging');

        $smtp = SmtpSetting::query()->firstOrNew();

        // A blank password field keeps the stored one; it is never echoed back to the page.
        if (blank($validated['smtp_password'] ?? null)) {
            unset($validated['smtp_password']);
            if (! $smtp->exists || blank($smtp->smtp_password)) {
                return redirect()->back()->withInput()->withErrors(['smtp_password' => 'The SMTP password is required.']);
            }
        }

        $smtp->fill($validated)->save();

        return redirect()->back()->with('success', 'SMTP settings updated');
    }

    /**
     * Send a plain test message through the saved SMTP settings.
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            Mail::raw(
                'This is a test email from '.config('app.name').'. If you received it, the SMTP settings are working.',
                fn (Message $message) => $message->to($validated['test_email'])->subject(config('app.name').' SMTP test'),
            );
        } catch (Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', 'Test email could not be sent: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Test email sent to '.$validated['test_email'].'.');
    }

    public function backupSettings(): View
    {
        return view('superadmin.settings.backup', [
            'backup' => BackupSetting::current(),
            'runs' => BackupRun::query()->with('triggeredBy')->latest()->limit(15)->get(),
        ]);
    }

    public function updateBackup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_time' => 'required|date_format:H:i',
            'retention_period' => 'required|in:7_days,14_days,30_days,60_days,90_days',
            'email_notification' => 'boolean',
            'backup_database' => 'boolean',
            'backup_files' => 'boolean',
            'backup_logs' => 'boolean',
        ]);

        foreach (['email_notification', 'backup_database', 'backup_files', 'backup_logs'] as $field) {
            $validated[$field] = $request->boolean($field);
        }

        if (! $validated['backup_database'] && ! $validated['backup_files'] && ! $validated['backup_logs']) {
            return redirect()->back()->withInput()->withErrors(['backup_database' => 'Select at least one item to back up.']);
        }

        $validated['backup_location'] = 'local';

        BackupSetting::query()->firstOrNew()->fill($validated)->save();

        return redirect()->back()->with('success', 'Backup settings updated');
    }

    /**
     * Queue a manual backup; the queue worker (drained by the scheduler) runs it.
     */
    public function runBackup(Request $request): RedirectResponse
    {
        if (BackupRun::query()->whereIn('status', ['queued', 'running'])->where('created_at', '>=', now()->subHour())->exists()) {
            return redirect()->back()->with('error', 'A backup is already in progress.');
        }

        $run = BackupRun::create(['trigger' => 'manual', 'status' => 'queued', 'triggered_by' => $request->user()->id]);

        RunBackupJob::dispatch($run);

        return redirect()->back()->with('success', 'Backup queued. It will appear as completed in the history once it has run.');
    }

    public function downloadBackup(BackupRun $backupRun, BackupService $backups): StreamedResponse
    {
        $path = $backups->archivePath($backupRun);

        abort_if($path === null, 404);

        return $backups->disk()->download($path, $backupRun->file_name);
    }

    public function destroyBackup(BackupRun $backupRun, BackupService $backups): RedirectResponse
    {
        if (in_array($backupRun->status, ['queued', 'running'], true)) {
            return redirect()->back()->with('error', 'This backup is still in progress.');
        }

        $backups->deleteArchive($backupRun);

        return redirect()->back()->with('success', 'Backup deleted.');
    }

    public function securitySettings(Request $request): View
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn (object $session) => [
                'id' => $session->id,
                'device' => $this->describeUserAgent((string) $session->user_agent),
                'icon' => $this->browserIcon((string) $session->user_agent),
                'ip' => $session->ip_address,
                'last_active' => Carbon::createFromTimestamp($session->last_activity),
                'current' => $session->id === $request->session()->getId(),
            ]);

        return view('superadmin.settings.security', [
            'security' => SecuritySetting::current(),
            'sessions' => $sessions,
        ]);
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_logout' => ['required', Rule::in(array_keys(SecuritySetting::AUTO_LOGOUT_MINUTES))],
            'login_attempts_limit' => 'required|integer|min:1|max:10',
            'account_lock_duration' => ['required', Rule::in(array_keys(SecuritySetting::LOCK_MINUTES))],
        ]);

        SecuritySetting::query()->firstOrNew()->fill($validated)->save();

        return redirect()->back()->with('success', 'Security settings updated');
    }

    /**
     * Sign one of the super admin's other devices out.
     */
    public function revokeSession(Request $request, string $session): RedirectResponse
    {
        if ($session === $request->session()->getId()) {
            return redirect()->back()->with('error', 'Use Logout to end the current session.');
        }

        DB::table('sessions')->where('id', $session)->where('user_id', $request->user()->id)->delete();

        return redirect()->back()->with('success', 'Session signed out.');
    }

    private function describeUserAgent(string $agent): string
    {
        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') => 'Opera',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Browser',
        };

        $platform = match (true) {
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Unknown device',
        };

        return "{$browser} on {$platform}";
    }

    private function browserIcon(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg/') => 'edge',
            str_contains($agent, 'OPR/') => 'opera',
            str_contains($agent, 'Firefox/') => 'firefox',
            str_contains($agent, 'Chrome/') => 'chrome',
            str_contains($agent, 'Safari/') => 'safari',
            default => 'chrome',
        };
    }
}
