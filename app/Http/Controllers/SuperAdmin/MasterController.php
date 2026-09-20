<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMode;
use App\Models\SocietyType;
use App\Models\UnitType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /** @var array<string, string> */
    private const TABS = [
        'society-types' => 'Society Types',
        'unit-types' => 'Unit Types',
        'payment-modes' => 'Payment Modes',
    ];

    public function index(Request $request)
    {
        $tab = $request->string('tab')->toString();
        if (! array_key_exists($tab, self::TABS)) {
            $tab = array_key_first(self::TABS);
        }

        $societyTypes = SocietyType::latest()->paginate(10, ['*'], 'society_types_page')->withQueryString();
        $unitTypes = UnitType::latest()->paginate(10, ['*'], 'unit_types_page')->withQueryString();
        $paymentModes = PaymentMode::latest()->paginate(10, ['*'], 'payment_modes_page')->withQueryString();

        return view('superadmin.master.index', [
            'tabs' => self::TABS,
            'tab' => $tab,
            'societyTypes' => $societyTypes,
            'unitTypes' => $unitTypes,
            'paymentModes' => $paymentModes,
        ]);
    }

    private function redirectToTab(string $tab, string $message): RedirectResponse
    {
        return redirect()->route('superadmin.masters.index', ['tab' => $tab])->with('success', $message);
    }

    public function storeSocietyType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        SocietyType::create($validated);

        return $this->redirectToTab('society-types', 'Society type created');
    }

    public function updateSocietyType(Request $request, SocietyType $societyType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $societyType->update($validated);

        return $this->redirectToTab('society-types', 'Society type updated');
    }

    public function destroySocietyType(SocietyType $societyType)
    {
        $societyType->delete();

        return $this->redirectToTab('society-types', 'Society type deleted');
    }

    public function storeUnitType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        UnitType::create($validated);

        return $this->redirectToTab('unit-types', 'Unit type created');
    }

    public function updateUnitType(Request $request, UnitType $unitType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $unitType->update($validated);

        return $this->redirectToTab('unit-types', 'Unit type updated');
    }

    public function destroyUnitType(UnitType $unitType)
    {
        $unitType->delete();

        return $this->redirectToTab('unit-types', 'Unit type deleted');
    }

    public function storePaymentMode(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        PaymentMode::create($validated);

        return $this->redirectToTab('payment-modes', 'Payment mode created');
    }

    public function updatePaymentMode(Request $request, PaymentMode $paymentMode)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $paymentMode->update($validated);

        return $this->redirectToTab('payment-modes', 'Payment mode updated');
    }

    public function destroyPaymentMode(PaymentMode $paymentMode)
    {
        $paymentMode->delete();

        return $this->redirectToTab('payment-modes', 'Payment mode deleted');
    }
}
