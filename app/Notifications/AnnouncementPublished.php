<?php

namespace App\Notifications;

use App\Models\Announcement;
use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Delivered per recipient for both announcements and notices. Channels come
 * from the source row (in-app always for notices; per delivery_channel for
 * announcements).
 */
class AnnouncementPublished extends Notification
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
            $mail->action('Open notices', route('society.notices.index'));
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
            'type' => $this->item instanceof Notice ? 'notice' : 'announcement',
            'title' => $this->item->title,
            'priority' => $this->item->priority,
            'item_id' => $this->item->id,
            'url' => route('society.notices.index'),
        ];
    }

    private function kindLabel(): string
    {
        return $this->item instanceof Notice ? 'Notice' : 'Announcement';
    }
}
