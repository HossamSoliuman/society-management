<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Society;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\SupportTicket;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSocieties = Society::count();
        $activeSocieties = Society::where('status', 'active')->count();
        $expiredSocieties = Society::where('subscription_status', 'expired')->count();
        $totalUnits = Society::sum('flats_count') + Society::sum('shops_count') + Society::sum('offices_count');

        $currentMonthRevenue = Payment::whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');

        $totalRevenue = Payment::sum('amount');

        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = Payment::whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('amount');
            $monthlyRevenue[] = [
                'month' => $date->format('M'),
                'amount' => $amount,
            ];
        }

        $societyStatus = [
            'active' => Society::where('status', 'active')->where('subscription_status', 'active')->count(),
            'expired' => Society::where('subscription_status', 'expired')->count(),
            'inactive' => Society::where('status', 'inactive')->count(),
        ];

        $subscriptionStatus = [
            'active' => Subscription::where('status', 'active')->count(),
            'expiring_soon' => Subscription::where('status', 'expiring_soon')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];

        $revenueByPlan = [
            ['name' => 'Premium Plan', 'amount' => 785120, 'percentage' => 62.9],
            ['name' => 'Standard Plan', 'amount' => 345780, 'percentage' => 27.7],
            ['name' => 'Basic Plan', 'amount' => 114960, 'percentage' => 9.2],
        ];

        $recentSocieties = Society::with('subscriptionPlan')->latest()->take(5)->get();
        $recentPayments = Payment::with('society')->latest()->take(5)->get();

        $totalUsers = User::count();
        $openTickets = SupportTicket::where('status', 'open')->count();

        return view('superadmin.dashboard.index', compact(
            'totalSocieties', 'activeSocieties', 'expiredSocieties', 'totalUnits',
            'currentMonthRevenue', 'totalRevenue', 'monthlyRevenue',
            'societyStatus', 'subscriptionStatus', 'revenueByPlan',
            'recentSocieties', 'recentPayments', 'totalUsers', 'openTickets'
        ));
    }
}
