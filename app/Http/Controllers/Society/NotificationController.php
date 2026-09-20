<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Support\NotificationKind;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Inbox of the signed-in society user's database notifications.
 */
class NotificationController extends Controller
{
    public const TABS = ['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'];

    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $user = $request->user();

        $tab = $request->string('tab')->toString();
        $tab = array_key_exists($tab, self::TABS) ? $tab : 'all';

        $type = $request->string('type')->toString();
        $type = in_array($type, NotificationKind::types(), true) ? $type : null;

        $notifications = $user->notifications()
            ->when($tab === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($tab === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when($type, fn ($q) => $q->where('data->type', $type))
            ->when($request->filled('q'), fn ($q) => $q->where('data->title', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', Carbon::parse($request->string('to'))))
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('society.notifications.index', [
            'society' => $society,
            'notifications' => $notifications,
            'tab' => $tab,
            'tabs' => self::TABS,
            'type' => $type,
            'typeLabels' => NotificationKind::labels(),
            'stats' => $this->stats($request),
            'byType' => $this->byType($request),
        ]);
    }

    /**
     * @return array{total: int, unread: int, read: int, today: int}
     */
    private function stats(Request $request): array
    {
        $user = $request->user();

        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Notification counts keyed by payload type, known types first.
     *
     * @return array<string, int>
     */
    private function byType(Request $request): array
    {
        $counts = $request->user()->notifications()
            ->reorder()
            ->select('data->type as type')
            ->selectRaw('count(*) as total')
            ->groupBy('data->type')
            ->pluck('total', 'type');

        $ordered = [];
        foreach (NotificationKind::types() as $type) {
            if (($counts[$type] ?? 0) > 0) {
                $ordered[$type] = (int) $counts[$type];
            }
        }

        $other = (int) $counts->except(NotificationKind::types())->sum();
        if ($other > 0) {
            $ordered['other'] = $other;
        }

        return $ordered;
    }
}
