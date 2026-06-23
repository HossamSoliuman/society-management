<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocietyType;
use App\Models\UnitType;
use App\Models\PaymentMode;

class MasterController extends Controller
{
    public function index()
    {
        $societyTypes = SocietyType::latest()->paginate(10, ['*'], 'society_types_page');
        $unitTypes = UnitType::latest()->paginate(10, ['*'], 'unit_types_page');
        $paymentModes = PaymentMode::latest()->paginate(10, ['*'], 'payment_modes_page');

        return view('superadmin.master.index', compact('societyTypes', 'unitTypes', 'paymentModes'));
    }

    public function storeSocietyType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        SocietyType::create($validated);
        return redirect()->route('superadmin.masters.index')->with('success', 'Society type created');
    }

    public function updateSocietyType(Request $request, SocietyType $societyType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $societyType->update($validated);
        return redirect()->route('superadmin.masters.index')->with('success', 'Society type updated');
    }

    public function destroySocietyType(SocietyType $societyType)
    {
        $societyType->delete();
        return redirect()->route('superadmin.masters.index')->with('success', 'Society type deleted');
    }

    public function storeUnitType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        UnitType::create($validated);
        return redirect()->route('superadmin.masters.index')->with('success', 'Unit type created');
    }

    public function updateUnitType(Request $request, UnitType $unitType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $unitType->update($validated);
        return redirect()->route('superadmin.masters.index')->with('success', 'Unit type updated');
    }

    public function destroyUnitType(UnitType $unitType)
    {
        $unitType->delete();
        return redirect()->route('superadmin.masters.index')->with('success', 'Unit type deleted');
    }

    public function storePaymentMode(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        PaymentMode::create($validated);
        return redirect()->route('superadmin.masters.index')->with('success', 'Payment mode created');
    }

    public function updatePaymentMode(Request $request, PaymentMode $paymentMode)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $paymentMode->update($validated);
        return redirect()->route('superadmin.masters.index')->with('success', 'Payment mode updated');
    }

    public function destroyPaymentMode(PaymentMode $paymentMode)
    {
        $paymentMode->delete();
        return redirect()->route('superadmin.masters.index')->with('success', 'Payment mode deleted');
    }
}
