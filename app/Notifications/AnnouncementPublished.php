<?php

namespace App\Notifications;

use App\Models\Announcement;
use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Delivered per recipient for both announcements and notices. Channels come
 * from the source row (in-app always for notices; per delivery_channel for
 * announcements). Queued so each channel is its own job: a failing SMTP or
 * SMS gateway cannot block in-app delivery or re-create rows on retry.
 */
class AnnouncementPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Announcement|Notice $item) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->item->channels();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $body = $this->item instanceof Notice
            ? ($this->item->short_description ?: strip_tags((string) $this->item->content))
            : $this->item->message;

        $mail = (new MailMessage)
            ->subject($this->kindLabel().': '.$this->item->title)
            ->greeting("Hello {$notifiable->name},")
            ->line($body);

        if ($notifiable->society_id) {
            $mail->action('Open '.strtolower($this->kindLabel()), self::targetUrl($this->type(), $this->item->id, $notifiable));
        }

        return $mail;
    }

    public function toSms(object $notifiable): string
    {
        $body = $this->item instanceof Notice
            ? ($this->item->short_description ?: strip_tags((string) $this->item->content))
            : $this->item->message;

        return Str::limit($this->item->title.': '.$body, 155);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type(),
            'title' => $this->item->title,
            'priority' => $this->item->priority,
            'item_id' => $this->item->id,
            'url' => self::targetUrl($this->type(), $this->item->id, $notifiable),
        ];
    }

    /**
     * Page showing one announcement or notice in the recipient's own panel:
     * the member portal for residents, the society panel for every other role.
     */
    public static function targetUrl(string $type, int $itemId, object $notifiable): string
    {
        $panel = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('member') ? 'member' : 'society';

        return route("{$panel}.".($type === 'notice' ? 'notices' : 'announcements').'.show', $itemId);
    }

    private function type(): string
    {
        return $this->item instanceof Notice ? 'notice' : 'announcement';
    }

    private function kindLabel(): string
    {
        return $this->item instanceof Notice ? 'Notice' : 'Announcement';
    }
}
