<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Society;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Subscription;

class ReportController extends Controller
{
    public function index()
    {
        return view('superadmin.report.index');
    }

    public function societyReport()
    {
        $societies = Society::with('subscriptionPlan')->latest()->get();
        return view('superadmin.report.society', compact('societies'));
    }

    public function revenueReport()
    {
        $payments = Payment::with('society')->where('status', 'success')->latest()->get();
        return view('superadmin.report.revenue', compact('payments'));
    }

    public function subscriptionReport()
    {
        $subscriptions = Subscription::with(['society', 'plan'])->latest()->get();
        return view('superadmin.report.subscription', compact('subscriptions'));
    }

    public function paymentReport()
    {
        $payments = Payment::with('society')->latest()->get();
        return view('superadmin.report.payment', compact('payments'));
    }
}
