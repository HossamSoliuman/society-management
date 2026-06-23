<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
}
