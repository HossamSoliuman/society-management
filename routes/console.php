<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:refresh-status')->dailyAt('00:15');
Schedule::command('subscriptions:send-renewal-alerts')->dailyAt('08:00');
Schedule::command('invoices:mark-overdue')->dailyAt('00:30');
Schedule::command('announcements:dispatch-scheduled')->everyMinute();
Schedule::command('bills:mark-overdue')->dailyAt('00:45');
Schedule::command('bills:apply-late-fees')->dailyAt('01:00');
Schedule::command('bills:send-reminders')->dailyAt('09:00');
Schedule::command('amc:send-expiry-alerts')->dailyAt('08:30');

// Shared hosting has no supervisor: drain the database queue from the scheduler instead.
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
