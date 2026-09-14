<?php

namespace App\Notifications\Channels;

use App\Contracts\SmsGateway;
use Illuminate\Notifications\Notification;

/**
 * Custom notification channel: notifications implement toSms($notifiable)
 * returning the text; the notifiable exposes `mobile`.
 */
class SmsChannel
{
    public function __construct(private readonly SmsGateway $gateway) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $mobile = $notifiable->routeNotificationFor('sms', $notification) ?? $notifiable->mobile ?? null;
        if (blank($mobile)) {
            return;
        }

        $this->gateway->send((string) $mobile, (string) $notification->toSms($notifiable));
    }
}
