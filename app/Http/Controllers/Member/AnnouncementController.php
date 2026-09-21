<?php

namespace App\Http\Controllers\Member;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Platform announcements sent to residents of this society (society-wide or
 * targeted at the "member" role).
 */
class AnnouncementController extends PortalController
{
    public function show(Request $request, Announcement $announcement): View
    {
        abort_unless(Announcement::query()->sent()->forUser($request->user())->whereKey($announcement->id)->exists(), 404);

        return view('member.announcements.show', [
            'member' => $this->currentMember(),
            'announcement' => $announcement,
        ]);
    }
}
