<?php

namespace Database\Seeders;

use App\Models\BillSetting;
use App\Models\ChargeHead;
use App\Models\LateFeeSetting;
use App\Models\NumberingSeries;
use App\Models\Society;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class Phase2SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::orderBy('id')->first();
        $societyId = $society?->id;

        $this->seedChargeHeads($societyId);
        $this->seedTaxes($societyId);
        $this->seedNumberingSeries($societyId);
        $this->seedBillSetting($societyId, $society);
        $this->seedLateFeeSetting($societyId);
    }

    private function seedChargeHeads(?int $societyId): void
    {
        // Exact demo rows from design §7.3.
        $rows = [
            ['Maintenance Charges', 'Monthly maintenance for the flat/unit', 'maintenance', 'per_flat', 2500, 'active'],
            ['Sinking Fund', 'Contribution towards future repairs', 'maintenance', 'per_flat', 500, 'active'],
            ['Reserve Fund', 'Reserve fund contribution', 'maintenance', 'per_flat', 300, 'active'],
            ['Water Charges', 'Water supply charges', 'utilities', 'per_flat', 200, 'active'],
            ['Parking Charges', 'Car/Bike parking charges', 'parking', 'per_slot', 400, 'active'],
            ['Club House Maintenance', 'Club house maintenance charges', 'amenities', 'per_flat', 150, 'active'],
            ['Lift Maintenance', 'Lift maintenance expenses', 'maintenance', 'per_flat', 250, 'active'],
            ['Generator Maintenance', 'Generator maintenance expenses', 'maintenance', 'per_flat', 150, 'active'],
            ['Pest Control', 'Pest control charges', 'maintenance', 'per_flat', 100, 'inactive'],
            ['Intercom Charges', 'Intercom & communication charges', 'utilities', 'per_flat', 50, 'inactive'],
        ];

        foreach ($rows as $index => [$name, $description, $category, $calc, $amount, $status]) {
            ChargeHead::create([
                'society_id' => $societyId,
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'type' => 'recurring',
                'calculation_type' => $calc,
                'default_amount' => $amount,
                'applies_to' => $status === 'active' ? 'All Bills' : '—',
                'status' => $status,
                'sort_order' => $index + 1,
            ]);
        }

        // Bring the totals up to the KPI figures (18 total, 16 active, 2 inactive).
        // 8 named active + 6 more "All Bills" = 14 "Used in This Month"; the
        // remaining 2 active heads apply to specific units only.
        ChargeHead::factory()->count(6)->create([
            'society_id' => $societyId,
            'status' => 'active',
            'applies_to' => 'All Bills',
            'sort_order' => 99,
        ]);
        ChargeHead::factory()->count(2)->create([
            'society_id' => $societyId,
            'status' => 'active',
            'applies_to' => 'Specific Units',
            'sort_order' => 99,
        ]);
    }

    private function seedTaxes(?int $societyId): void
    {
        $rows = [
            ['CGST (Central GST)', 9.00, 'Central Goods and Services Tax'],
            ['SGST (State GST)', 9.00, 'State Goods and Services Tax'],
        ];

        // Fixed timestamp so the "Last Updated" KPI reads "30 May 2025" per design.
        $updatedAt = '2025-05-30 10:00:00';

        foreach ($rows as $index => [$name, $rate, $description]) {
            Tax::create([
                'society_id' => $societyId,
                'name' => $name,
                'tax_type' => 'percentage',
                'rate' => $rate,
                'apply_on' => 'All Charge Heads',
                'description' => $description,
                'status' => 'active',
                'sort_order' => $index + 1,
                'created_at' => $updatedAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }

    private function seedNumberingSeries(?int $societyId): void
    {
        // Exact demo rows from design §12.2.
        $rows = [
            ['maintenance_bill', true, 'MB-', 513, 'Maintenance bill numbering series'],
            ['receipt', false, 'RCPT-', 842, 'Receipt numbering series'],
            ['credit_note', false, 'CN-', 126, 'Credit note numbering series'],
            ['debit_note', false, 'DN-', 58, 'Debit note numbering series'],
            ['refund', false, 'RFND-', 41, 'Refund numbering series'],
        ];

        foreach ($rows as [$type, $isDefault, $prefix, $next, $description]) {
            NumberingSeries::create([
                'society_id' => $societyId,
                'document_type' => $type,
                'is_default' => $isDefault,
                'prefix' => $prefix,
                'format' => 'YYYY-#####',
                'next_number' => $next,
                'reset_frequency' => 'yearly',
                'financial_year' => '2025-2026',
                'start_date' => '2025-04-01',
                'description' => $description,
                'status' => 'active',
            ]);
        }
    }

    private function seedBillSetting(?int $societyId, ?Society $society): void
    {
        BillSetting::create([
            'society_id' => $societyId,
            // General
            'default_bill_type' => 'Monthly Maintenance',
            'default_bill_cycle' => 'Current Month',
            'bill_generation_date' => '1st of Every Month',
            'due_date_days' => 15,
            'grace_period_days' => 5,
            'round_off' => 'Round to Nearest Rupee',
            'allow_zero_amount_bills' => true,
            // Calculation
            'calculation_method' => 'flat_based',
            'include_sinking_fund' => true,
            'include_reserve_fund' => true,
            'adjust_advance_amount' => true,
            'minimum_bill_amount' => 100.00,
            'include_previous_dues' => true,
            // Other
            'allow_partial_payments' => true,
            'auto_email_bill' => true,
            'auto_sms_bill' => false,
            // Bill numbering format
            'bill_number_prefix' => 'MB',
            'bill_number_format' => 'YYYYMMDD-XXXX',
            'next_sequence_number' => '000157',
            // Notes
            'terms_conditions' => "1. Please make payment within the due date to avoid late fee.\n2. This is a system generated bill, no signature required.\n3. For any queries, please contact the management office.",
            'footer_note' => 'Thank you for being a valued member.',
            // Bill Design
            'template' => 'modern',
            'society_name' => $society->name ?? 'Green View Residency',
            'address' => 'Plot No. 45, Sector 12, Navi Mumbai - 400705, Maharashtra, India',
            'phone' => '+91 98765 43210',
            'email' => 'greenview@society.in',
            'website' => 'www.greenviewresidency.in',
            'show_logo' => true,
            'show_address' => true,
            'show_contact' => true,
            'show_gstin' => false,
            'primary_color' => '#FF6A00',
            'secondary_color' => '#1F2937',
            'text_color' => '#374151',
            'show_thank_you' => true,
            'show_footer_note' => true,
            'show_qr' => true,
            'show_terms' => true,
        ]);
    }

    private function seedLateFeeSetting(?int $societyId): void
    {
        LateFeeSetting::create([
            'society_id' => $societyId,
            'enable_late_fee' => true,
            'grace_period_days' => 15,
            'late_fee_type' => 'percentage',
            'late_fee_percent' => 2.00,
            'max_late_fee_cap' => 500.00,
            'compounding' => 'Monthly',
            'enable_interest' => true,
            'interest_calc_type' => 'simple',
            'interest_rate_annual' => 18.00,
            'apply_interest_after_days' => 30,
            'interest_calc_on' => 'Outstanding Amount (Including Previous Dues)',
            'round_off_interest' => 'Round to Nearest Rupee',
        ]);
    }
}
