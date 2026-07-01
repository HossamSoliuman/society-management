<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Society;

class DashboardController extends Controller
{
    public function index()
    {
        $society = Society::firstOrFail();

        // Total Members is DB-backed; the remaining dashboard KPIs are design
        // snapshot figures (no collections/complaints models exist in Phase 1).
        $stats = [
            'total_members' => Member::where('society_id', $society->id)->count(),
            'new_members' => 8,
            'total_units' => 120,
            'occupancy_pct' => 92,
            'monthly_collections' => 245800,
            'collected_pct' => 88,
            'pending_dues' => 35600,
            'pending_members' => 12,
            'open_complaints' => 18,
            'high_priority' => 6,
        ];

        $collection = [
            'collected' => 245800,
            'pending' => 35600,
            'overdue' => 18200,
            'total_demand' => 279600,
        ];

        $revenue = [
            'points' => [100000, 160000, 220000, 170000, 240000, 240000],
            'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'ytd' => 1467200,
            'vs_last_year' => 18.6,
        ];

        $activities = [
            ['icon' => 'fa-indian-rupee-sign', 'variant' => 'is-success', 'title' => 'Payment received from', 'subtitle' => 'Flat A-101', 'time' => '30 May, 11:30 AM', 'amount' => '5,200'],
            ['icon' => 'fa-file-lines', 'variant' => 'is-warning', 'title' => 'New complaint submitted', 'subtitle' => 'Water leakage in B-203', 'time' => '30 May, 10:15 AM', 'amount' => null],
            ['icon' => 'fa-user-plus', 'variant' => 'is-purple', 'title' => 'New member added', 'subtitle' => 'Mr. Arvind Kumar (A-302)', 'time' => '30 May, 09:45 AM', 'amount' => null],
            ['icon' => 'fa-file-invoice', 'variant' => 'is-info', 'title' => 'Maintenance invoice generated', 'subtitle' => 'June 2025', 'time' => '30 May, 09:00 AM', 'amount' => null],
            ['icon' => 'fa-car', 'variant' => 'is-danger', 'title' => 'New vehicle registered', 'subtitle' => 'MH 14 AB 1234 (A-101)', 'time' => '29 May, 06:20 PM', 'amount' => null],
        ];

        $notices = [
            ['icon' => 'fa-bullhorn', 'variant' => 'is-danger', 'title' => 'Water Supply Maintenance', 'desc' => 'Water supply will be closed on 2nd June from 10:00 AM to 2:00 PM.', 'date' => '30 May 2025'],
            ['icon' => 'fa-people-group', 'variant' => 'is-warning', 'title' => 'Annual General Meeting', 'desc' => 'AGM will be held on 15th June 2025 at 6:00 PM in the Community Hall.', 'date' => '28 May 2025'],
            ['icon' => 'fa-elevator', 'variant' => 'is-info', 'title' => 'Lift Maintenance', 'desc' => 'Lift service scheduled on 5th June. Please cooperate.', 'date' => '27 May 2025'],
        ];

        $complaintSummary = [
            'open' => 18,
            'in_progress' => 8,
            'resolved' => 4,
        ];

        $occupancy = [
            'occupied' => 110,
            'vacant' => 10,
        ];

        $societyInfo = [
            'name' => 'Green Meadows CHS',
            'address' => 'Sector 15, Nerul, Navi Mumbai',
            'established' => 2018,
            'towers' => 4,
            'floors' => 12,
        ];

        return view('society.dashboard.index', compact(
            'stats', 'collection', 'revenue', 'activities', 'notices',
            'complaintSummary', 'occupancy', 'societyInfo'
        ));
    }
}
