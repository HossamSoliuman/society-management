<?php

namespace App\Console\Commands;

use App\Jobs\DeliverAnnouncement;
use App\Models\Announcement;
use App\Models\Notice;
use Illuminate\Console\Command;

class DispatchScheduledAnnouncements extends Command
{
    protected $signature = 'announcements:dispatch-scheduled';

    protected $description = 'Queue delivery for scheduled announcements and notices whose time has come';

    public function handle(): int
    {
        $count = 0;

        Announcement::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->each(function (Announcement $announcement) use (&$count) {
                // Claim the row first so a second tick never double-sends.
                $announcement->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
                DeliverAnnouncement::dispatch($announcement);
                $count++;
            });

        Notice::query()
            ->where('status', 'scheduled')
            ->where('publish_at', '<=', now())
            ->each(function (Notice $notice) use (&$count) {
                $notice->forceFill(['status' => 'published'])->save();
                DeliverAnnouncement::dispatch($notice);
                $count++;
            });

        Notice::query()
            ->where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Dispatched {$count} scheduled item(s).");

        return self::SUCCESS;
    }
}
