<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'due_date_days' => 'integer',
            'grace_period_days' => 'integer',
            'allow_zero_amount_bills' => 'boolean',
            'include_sinking_fund' => 'boolean',
            'include_reserve_fund' => 'boolean',
            'adjust_advance_amount' => 'boolean',
            'minimum_bill_amount' => 'decimal:2',
            'include_previous_dues' => 'boolean',
            'allow_partial_payments' => 'boolean',
            'auto_email_bill' => 'boolean',
            'auto_sms_bill' => 'boolean',
            'show_society_details' => 'boolean',
            'show_member_details' => 'boolean',
            'show_flat_details' => 'boolean',
            'show_bill_summary' => 'boolean',
            'show_previous_balance' => 'boolean',
            'show_payment_history' => 'boolean',
            'show_charge_head_description' => 'boolean',
            'show_notes' => 'boolean',
            'show_payment_qr' => 'boolean',
            'amount_decimal_places' => 'integer',
            'show_logo' => 'boolean',
            'show_address' => 'boolean',
            'show_contact' => 'boolean',
            'show_gstin' => 'boolean',
            'show_thank_you' => 'boolean',
            'show_footer_note' => 'boolean',
            'show_qr' => 'boolean',
            'show_terms' => 'boolean',
            'reminder_days_before_due' => 'array',
            'reminder_days_after_due' => 'array',
            'notification_settings' => 'array',
        ];
    }

    /**
     * Billing events that can trigger member notifications, with defaults.
     *
     * @return array<string, array{title: string, desc: string, channels: array<string, bool>, template: string}>
     */
    public static function notificationEventDefaults(): array
    {
        return [
            'bill_generated' => [
                'title' => 'Bill Generated',
                'desc' => 'Sent to members when a new bill is generated.',
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false],
                'template' => 'Dear {member_name}, your maintenance bill {bill_no} of {amount} for {bill_month} has been generated. Due date: {due_date}.',
            ],
            'payment_received' => [
                'title' => 'Payment Received',
                'desc' => 'Sent when a payment is recorded against a bill.',
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false],
                'template' => 'Dear {member_name}, we have received your payment of {amount} against bill {bill_no}. Thank you!',
            ],
            'payment_reminder' => [
                'title' => 'Payment Reminder',
                'desc' => 'Sent before the due date as a friendly reminder.',
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false],
                'template' => 'Dear {member_name}, this is a reminder that bill {bill_no} of {amount} is due on {due_date}. Please pay on time to avoid late fee.',
            ],
            'overdue_reminder' => [
                'title' => 'Overdue Reminder',
                'desc' => 'Sent after the due date for unpaid bills.',
                'channels' => ['email' => true, 'sms' => true, 'whatsapp' => false],
                'template' => 'Dear {member_name}, bill {bill_no} of {amount} is overdue. A late fee may now apply. Please clear your dues at the earliest.',
            ],
        ];
    }

    /**
     * Effective (saved ∪ default) notification settings for one event.
     *
     * @return array{title: string, desc: string, channels: array<string, bool>, template: string}
     */
    public function notificationEvent(string $key): array
    {
        $defaults = static::notificationEventDefaults()[$key];
        $saved = $this->notification_settings[$key] ?? [];

        return [
            'title' => $defaults['title'],
            'desc' => $defaults['desc'],
            'channels' => array_merge($defaults['channels'], $saved['channels'] ?? []),
            'template' => $saved['template'] ?? $defaults['template'],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function reminderDaysBeforeDue(): array
    {
        return array_values(array_map('intval', $this->reminder_days_before_due ?? [3]));
    }

    /**
     * @return array<int, int>
     */
    public function reminderDaysAfterDue(): array
    {
        return array_values(array_map('intval', $this->reminder_days_after_due ?? [1, 7]));
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }
}
