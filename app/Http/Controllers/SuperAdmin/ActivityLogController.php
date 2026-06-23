<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemLog;

class ActivityLogController extends Controller
{
    public function userActivities()
    {
        $activities = ActivityLog::latest()->paginate(10);

        return view('superadmin.logs.user-activities', compact('activities'));
    }

    public function systemLogs()
    {
        $logs = SystemLog::latest()->paginate(10);

        return view('superadmin.logs.system-logs', compact('logs'));
    }

    public function auditTrail()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(20);

        return view('superadmin.logs.audit-trail', compact('logs'));
    }
}
