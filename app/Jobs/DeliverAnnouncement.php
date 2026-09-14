<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\Notice;
use App\Notifications\AnnouncementPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Fan an announcement or notice out to every targeted user through the
 * channels the row asks for (in-app, email, SMS).
 */
class DeliverAnnouncement implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Announcement|Notice $item) {}

    public function handle(): void
    {
        $item = $this->item->fresh();
        if (! $item) {
            return;
        }

        $delivered = 0;

        $item->recipientsQuery()->orderBy('id')->chunkById(100, function ($users) use ($item, &$delivered) {
            foreach ($users as $user) {
                $user->notify(new AnnouncementPublished($item));
                $delivered++;
            }
        });

        $update = ['delivered_count' => $delivered, 'estimated_recipients' => $delivered];
        if ($item instanceof Announcement) {
            $update += ['status' => 'sent', 'sent_at' => now()];
        } else {
            $update += ['status' => 'published', 'delivered_at' => now()];
        }

        $item->forceFill($update)->save();
    }
}
