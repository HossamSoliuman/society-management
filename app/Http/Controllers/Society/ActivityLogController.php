<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Audit trail of everything recorded against the signed-in user's society.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $base = ActivityLog::query()->where('society_id', $society->id);

        $logs = (clone $base)
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->string('module')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('user'), fn ($q) => $q->where('user_name', $request->string('user')))
            ->when($request->filled('from'), fn ($q) => $q->where('created_at', '>=', Carbon::parse($request->string('from'))->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('created_at', '<=', Carbon::parse($request->string('to'))->endOfDay()))
            ->when($request->filled('q'), fn ($q) => $q->where('description', 'like', '%'.$request->string('q').'%'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('society.activity-logs.index', [
            'logs' => $logs,
            'modules' => (clone $base)->distinct()->orderBy('module')->pluck('module'),
            'actions' => (clone $base)->distinct()->orderBy('action')->pluck('action'),
            'users' => (clone $base)->whereNotNull('user_name')->distinct()->orderBy('user_name')->pluck('user_name'),
            'stats' => [
                'today' => (clone $base)->where('created_at', '>=', Carbon::today())->count(),
                'week' => (clone $base)->where('created_at', '>=', Carbon::today()->subDays(6))->count(),
                'users' => (clone $base)->where('created_at', '>=', Carbon::today()->subDays(6))->whereNotNull('user_id')->distinct()->count('user_id'),
                'failed' => (clone $base)->where('status', 'failed')->where('created_at', '>=', Carbon::today()->subDays(29))->count(),
            ],
        ]);
    }
}
