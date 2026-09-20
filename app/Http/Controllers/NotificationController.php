<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Header bell actions shared by every authenticated panel.
 */
class NotificationController extends Controller
{
    /**
     * Mark one of the signed-in user's notifications as read and jump to its target.
     */
    public function open(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $item */
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();

        $item->markAsRead();

        $url = $item->data['url'] ?? null;

        return is_string($url) && $url !== '' ? redirect()->to($url) : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Mark one notification as read without leaving the current page.
     */
    public function read(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->delete();

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Delete every notification the user has already read.
     */
    public function clearRead(Request $request): RedirectResponse
    {
        $deleted = $request->user()->readNotifications()->delete();

        return back()->with('success', $deleted > 0 ? "{$deleted} read notification".($deleted === 1 ? '' : 's').' cleared.' : 'No read notifications to clear.');
    }
}
