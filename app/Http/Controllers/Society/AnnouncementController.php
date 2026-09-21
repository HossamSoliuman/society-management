<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Announcements sent by the platform to this society (or all societies)
 * and the signed-in user's role; reached from the notification bell.
 */
class AnnouncementController extends Controller
{
    public function show(Request $request, Announcement $announcement): View
    {
        $user = $request->user();

        abort_unless(Announcement::query()->sent()->forUser($user)->whereKey($announcement->id)->exists(), 404);

        return view('society.announcements.show', [
            'society' => $this->currentSociety(),
            'announcement' => $announcement->load('society'),
        ]);
    }
}
