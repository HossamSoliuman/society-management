<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\BillSetting;
use App\Models\ChargeHead;
use App\Models\LateFeeSetting;
use App\Models\Member;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillSettingController extends Controller
{
    public function general(): View
    {
        $society = Society::first();
        $settings = $this->settings($society);

        return view('society.billing.settings.general', compact('society', 'settings'));
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $settings = $this->settings(Society::first());

        $data = $request->validate([
            'default_bill_type' => ['nullable', 'string'],
            'default_bill_cycle' => ['nullable', 'string'],
            'bill_generation_date' => ['nullable', 'string'],
            'due_date_days' => ['required', 'integer', 'min:0'],
            'grace_period_days' => ['required', 'integer', 'min:0'],
            'round_off' => ['nullable', 'string'],
            'allow_zero_amount_bills' => ['nullable', 'boolean'],
            'calculation_method' => ['required', 'in:flat_based,area_based,unit_based,custom'],
            'include_sinking_fund' => ['nullable', 'boolean'],
            'include_reserve_fund' => ['nullable', 'boolean'],
            'adjust_advance_amount' => ['nullable', 'boolean'],
            'minimum_bill_amount' => ['required', 'numeric', 'min:0'],
            'include_previous_dues' => ['nullable', 'boolean'],
            'default_payment_mode' => ['nullable', 'string'],
            'default_collection_account' => ['nullable', 'string'],
            'allow_partial_payments' => ['nullable', 'boolean'],
            'auto_email_bill' => ['nullable', 'boolean'],
            'auto_sms_bill' => ['nullable', 'boolean'],
            'show_society_details' => ['nullable', 'boolean'],
            'show_member_details' => ['nullable', 'boolean'],
            'show_flat_details' => ['nullable', 'boolean'],
            'show_bill_summary' => ['nullable', 'boolean'],
            'show_previous_balance' => ['nullable', 'boolean'],
            'show_payment_history' => ['nullable', 'boolean'],
            'show_charge_head_description' => ['nullable', 'boolean'],
            'show_notes' => ['nullable', 'boolean'],
            'show_payment_qr' => ['nullable', 'boolean'],
            'currency_format' => ['nullable', 'string'],
            'amount_decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'bill_number_prefix' => ['nullable', 'string', 'max:20'],
            'bill_number_format' => ['nullable', 'string', 'max:50'],
            'next_sequence_number' => ['nullable', 'string', 'max:20'],
            'terms_conditions' => ['nullable', 'string'],
            'footer_note' => ['nullable', 'string'],
        ]);

        $settings->update($this->withToggleDefaults($data, [
            'allow_zero_amount_bills', 'include_sinking_fund', 'include_reserve_fund',
            'adjust_advance_amount', 'include_previous_dues', 'allow_partial_payments',
            'auto_email_bill', 'auto_sms_bill', 'show_society_details', 'show_member_details',
            'show_flat_details', 'show_bill_summary', 'show_previous_balance', 'show_payment_history',
            'show_charge_head_description', 'show_notes', 'show_payment_qr',
        ]));

        return redirect()->route('society.billing.settings.general')
            ->with('success', 'Bill settings saved successfully.');
    }

    public function design(): View
    {
        $society = Society::first();
        $settings = $this->settings($society);

        $chargeHeads = ChargeHead::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return view('society.billing.settings.design', compact('society', 'settings', 'chargeHeads'));
    }

    public function updateDesign(Request $request): RedirectResponse
    {
        $settings = $this->settings(Society::first());

        $data = $request->validate([
            'template' => ['required', 'in:modern,classic,compact,minimal'],
            'society_name' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'website' => ['nullable', 'string'],
            'show_logo' => ['nullable', 'boolean'],
            'show_address' => ['nullable', 'boolean'],
            'show_contact' => ['nullable', 'boolean'],
            'show_gstin' => ['nullable', 'boolean'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'show_thank_you' => ['nullable', 'boolean'],
            'show_footer_note' => ['nullable', 'boolean'],
            'show_qr' => ['nullable', 'boolean'],
            'show_terms' => ['nullable', 'boolean'],
        ]);

        $settings->update($this->withToggleDefaults($data, [
            'show_logo', 'show_address', 'show_contact', 'show_gstin',
            'show_thank_you', 'show_footer_note', 'show_qr', 'show_terms',
        ]));

        return redirect()->route('society.billing.settings.design')
            ->with('success', 'Bill design saved successfully.');
    }

    public function lateFee(): View
    {
        $society = Society::first();
        $lateFee = $this->lateFeeSettings($society);

        $members = Member::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('name')
            ->get();

        $chargeHeads = ChargeHead::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('sort_order')
            ->get();

        return view('society.billing.settings.late-fee', compact('society', 'lateFee', 'members', 'chargeHeads'));
    }

    public function updateLateFee(Request $request): RedirectResponse
    {
        $lateFee = $this->lateFeeSettings(Society::first());

        $data = $request->validate([
            'enable_late_fee' => ['nullable', 'boolean'],
            'grace_period_days' => ['required', 'integer', 'min:0'],
            'late_fee_type' => ['required', 'in:percentage,flat'],
            'late_fee_percent' => ['nullable', 'numeric', 'min:0'],
            'late_fee_flat' => ['nullable', 'numeric', 'min:0'],
            'max_late_fee_cap' => ['nullable', 'numeric', 'min:0'],
            'compounding' => ['nullable', 'string'],
            'enable_interest' => ['nullable', 'boolean'],
            'interest_calc_type' => ['required', 'in:simple,compound'],
            'interest_rate_annual' => ['nullable', 'numeric', 'min:0'],
            'apply_interest_after_days' => ['required', 'integer', 'min:0'],
            'interest_calc_on' => ['nullable', 'string'],
            'round_off_interest' => ['nullable', 'string'],
            'exempt_members' => ['nullable', 'array'],
            'exempt_charge_heads' => ['nullable', 'array'],
            'exempt_bill_types' => ['nullable', 'array'],
        ]);

        $data = $this->withToggleDefaults($data, ['enable_late_fee', 'enable_interest']);
        $data['exempt_members'] = $request->input('exempt_members', []);
        $data['exempt_charge_heads'] = $request->input('exempt_charge_heads', []);
        $data['exempt_bill_types'] = $request->input('exempt_bill_types', []);

        $lateFee->update($data);

        return redirect()->route('society.billing.settings.late-fee')
            ->with('success', 'Late fee settings saved successfully.');
    }

    public function notifications(): View
    {
        $society = Society::first();
        $settings = $this->settings($society);

        return view('society.billing.settings.notifications', compact('society', 'settings'));
    }

    private function settings(?Society $society): BillSetting
    {
        return BillSetting::firstOrCreate(['society_id' => $society?->id]);
    }

    private function lateFeeSettings(?Society $society): LateFeeSetting
    {
        return LateFeeSetting::firstOrCreate(['society_id' => $society?->id]);
    }

    /**
     * Unchecked toggles/checkboxes are absent from the payload; coerce them to false.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $toggles
     * @return array<string, mixed>
     */
    private function withToggleDefaults(array $data, array $toggles): array
    {
        foreach ($toggles as $toggle) {
            $data[$toggle] = ! empty($data[$toggle]);
        }

        return $data;
    }
}
