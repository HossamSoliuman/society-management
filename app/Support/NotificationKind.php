<?php

namespace App\Support;

/**
 * Presentation metadata for the `type` key stored in database notification
 * payloads, shared by the header bell and the notifications page.
 */
final class NotificationKind
{
    /**
     * @var array<string, array{label: string, icon: string, variant: string}>
     */
    private const KINDS = [
        'announcement' => ['label' => 'Announcement', 'icon' => 'fa-bullhorn', 'variant' => 'primary'],
        'notice' => ['label' => 'Notice', 'icon' => 'fa-clipboard-list', 'variant' => 'info'],
        'ticket_created' => ['label' => 'New Ticket', 'icon' => 'fa-headset', 'variant' => 'purple'],
        'ticket_replied' => ['label' => 'Ticket Reply', 'icon' => 'fa-reply', 'variant' => 'purple'],
        'ticket_status' => ['label' => 'Ticket Status', 'icon' => 'fa-clipboard-check', 'variant' => 'success'],
        'subscription_renewal' => ['label' => 'Subscription', 'icon' => 'fa-rotate', 'variant' => 'warning'],
        'amc_expiry' => ['label' => 'AMC Expiry', 'icon' => 'fa-file-contract', 'variant' => 'danger'],
    ];

    private const FALLBACK = ['label' => 'Notification', 'icon' => 'fa-bell', 'variant' => 'secondary'];

    /**
     * Known type keys, in display order.
     *
     * @return list<string>
     */
    public static function types(): array
    {
        return array_keys(self::KINDS);
    }

    /**
     * Type => label map for filter dropdowns.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return array_map(fn (array $kind) => $kind['label'], self::KINDS);
    }

    public static function label(?string $type): string
    {
        return (self::KINDS[$type] ?? self::FALLBACK)['label'];
    }

    public static function icon(?string $type): string
    {
        return (self::KINDS[$type] ?? self::FALLBACK)['icon'];
    }

    public static function variant(?string $type): string
    {
        return (self::KINDS[$type] ?? self::FALLBACK)['variant'];
    }
}
