<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Contracts\SmsGateway;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Services\Payments\FakeGateway;
use App\Services\Payments\RazorpayGateway;
use App\Services\Sms\FakeSmsGateway;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, function () {
            return match (config('services.sms.driver', 'log')) {
                'fake' => new FakeSmsGateway,
                default => new LogSmsGateway,
            };
        });

        $this->app->singleton(PaymentGateway::class, function () {
            return match (config('services.payment_gateway.driver', 'fake')) {
                'razorpay' => new RazorpayGateway(
                    (string) config('services.razorpay.key'),
                    (string) config('services.razorpay.secret'),
                    config('services.razorpay.webhook_secret'),
                ),
                default => new FakeGateway,
            };
        });
    }

    public function boot(): void
    {
        View::composer('superadmin.*', function ($view) {
            if (Auth::check()) {
                $view->with('authUser', Auth::user());
            }
        });

        $this->applySmtpSettings();
        $this->registerRouteBindings();

        Notification::resolved(function (ChannelManager $manager) {
            $manager->extend('sms', fn ($app) => new SmsChannel($app->make(SmsGateway::class)));
        });
    }

    /**
     * {teamUser}: a user of the signed-in admin's own society (404 otherwise),
     * resolved before form-request validation runs.
     */
    private function registerRouteBindings(): void
    {
        Route::bind('teamUser', function (string $value): User {
            return User::query()
                ->whereKey($value)
                ->where('society_id', Auth::user()?->society_id ?? -1)
                ->firstOrFail();
        });
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
