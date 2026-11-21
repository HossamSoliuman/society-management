<?php

namespace App\Notifications;

use App\Models\BackupRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackupCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly BackupRun $run) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->greeting("Hello {$notifiable->name},");

        if ($this->run->status === 'completed') {
            return $message
                ->subject('Backup completed')
                ->line("The {$this->run->trigger} backup finished at {$this->run->completed_at?->format('d M Y H:i')}.")
                ->line('Archive: '.$this->run->file_name.' ('.$this->run->sizeLabel().', '.implode(', ', $this->run->contents ?? []).').')
                ->action('View backups', route('superadmin.settings.backup'));
        }

        return $message
            ->error()
            ->subject('Backup failed')
            ->line("The {$this->run->trigger} backup failed at {$this->run->completed_at?->format('d M Y H:i')}.")
            ->line('Reason: '.$this->run->error)
            ->action('Open backup settings', route('superadmin.settings.backup'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'backup',
            'title' => $this->run->status === 'completed' ? 'Backup completed ('.$this->run->sizeLabel().')' : 'Backup failed',
            'backup_run_id' => $this->run->id,
            'url' => route('superadmin.settings.backup'),
        ];
    }
}
