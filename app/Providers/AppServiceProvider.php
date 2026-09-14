<?php

namespace App\Providers;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('superadmin.*', function ($view) {
            if (Auth::check()) {
                $view->with('authUser', Auth::user());
            }
        });

        $this->applySmtpSettings();
    }

    /**
     * Let the super-admin SMTP Settings row override the mail config so
     * outbound mail works without editing .env on the host.
     */
    private function applySmtpSettings(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        try {
            if (! Schema::hasTable('smtp_settings')) {
                return;
            }

            $smtp = SmtpSetting::query()->first();
        } catch (Throwable) {
            return;
        }

        if (! $smtp || blank($smtp->smtp_host) || blank($smtp->smtp_username)) {
            return;
        }

        $encryption = strtolower((string) $smtp->encryption);
        $scheme = match (true) {
            str_contains($encryption, 'ssl') && ! str_contains($encryption, 'start') => 'smtps',
            default => 'smtp',
        };

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.host' => $smtp->smtp_host,
            'mail.mailers.smtp.port' => (int) $smtp->smtp_port,
            'mail.mailers.smtp.username' => $smtp->smtp_username,
            'mail.mailers.smtp.password' => $smtp->smtp_password,
            'mail.from.address' => $smtp->from_email ?: config('mail.from.address'),
            'mail.from.name' => $smtp->from_name ?: config('mail.from.name'),
        ]);

        if ($smtp->reply_to_email) {
            config(['mail.reply_to' => ['address' => $smtp->reply_to_email, 'name' => $smtp->from_name]]);
        }
    }
}
