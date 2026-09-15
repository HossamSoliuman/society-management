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
