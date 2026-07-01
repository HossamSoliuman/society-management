<?php

use App\Models\BillSetting;
use App\Models\ChargeHead;
use App\Models\LateFeeSetting;
use App\Models\NumberingSeries;
use App\Models\Society;
use App\Models\Tax;
use App\Models\User;
use Database\Seeders\Phase2SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->society = Society::create([
        'name' => 'Green View Residency',
        'prefix' => 'GVR',
        'status' => 'active',
    ]);
    $this->seed(Phase2SettingsSeeder::class);
});

it('loads each bill settings page', function (string $route) {
    $this->actingAs($this->user)->get(route($route))->assertOk();
})->with([
    'society.billing.settings.general',
    'society.billing.settings.charge-heads',
    'society.billing.settings.design',
    'society.billing.settings.late-fee',
    'society.billing.settings.taxes',
    'society.billing.settings.notifications',
    'society.billing.settings.numbering',
]);

it('shows the charge head KPIs and seeded rows', function () {
    $this->actingAs($this->user)->get(route('society.billing.settings.charge-heads'))
        ->assertOk()
        ->assertSee('Charge Heads')
        ->assertSee('18')            // total
        ->assertSee('16')            // active
        ->assertSee('Maintenance Charges')
        ->assertSee('Monthly maintenance for the flat/unit');
});

it('shows the taxes with the combined rate', function () {
    $this->actingAs($this->user)->get(route('society.billing.settings.taxes'))
        ->assertOk()
        ->assertSee('CGST (Central GST)')
        ->assertSee('SGST (State GST)')
        ->assertSee('18.00%');
});

it('shows the numbering series sample numbers', function () {
    $this->actingAs($this->user)->get(route('society.billing.settings.numbering'))
        ->assertOk()
        ->assertSee('MB-2025-000513')   // sample of next number
        ->assertSee('MB-2025-000512');  // last generated
});

it('stores a new charge head', function () {
    $this->actingAs($this->user)->post(route('society.billing.settings.charge-heads.store'), [
        'name' => 'Security Charges',
        'description' => 'Security staff charges',
        'category' => 'maintenance',
        'type' => 'recurring',
        'calculation_type' => 'per_flat',
        'default_amount' => 350,
        'applies_to' => 'All Bills',
        'status' => 'active',
    ])->assertRedirect(route('society.billing.settings.charge-heads'));

    expect(ChargeHead::where('name', 'Security Charges')->exists())->toBeTrue();
});

it('updates a charge head', function () {
    $chargeHead = ChargeHead::where('name', 'Water Charges')->first();

    $this->actingAs($this->user)->put(route('society.billing.settings.charge-heads.update', $chargeHead), [
        'name' => 'Water & Sewage Charges',
        'category' => 'utilities',
        'type' => 'recurring',
        'calculation_type' => 'per_flat',
        'default_amount' => 250,
        'status' => 'active',
    ])->assertRedirect();

    expect($chargeHead->fresh()->name)->toBe('Water & Sewage Charges');
});

it('deletes a charge head', function () {
    $chargeHead = ChargeHead::where('name', 'Pest Control')->first();

    $this->actingAs($this->user)->delete(route('society.billing.settings.charge-heads.destroy', $chargeHead))
        ->assertRedirect();

    expect(ChargeHead::find($chargeHead->id))->toBeNull();
});

it('stores a new tax', function () {
    $this->actingAs($this->user)->post(route('society.billing.settings.taxes.store'), [
        'name' => 'IGST',
        'tax_type' => 'percentage',
        'rate' => 18,
        'apply_on' => 'All Charge Heads',
        'status' => 'active',
    ])->assertRedirect();

    expect(Tax::where('name', 'IGST')->exists())->toBeTrue();
});

it('stores a new numbering series', function () {
    $this->actingAs($this->user)->post(route('society.billing.settings.numbering.store'), [
        'document_type' => 'receipt',
        'prefix' => 'RC-',
        'format' => 'YYYY-#####',
        'next_number' => 1,
        'reset_frequency' => 'yearly',
        'financial_year' => '2025-2026',
        'status' => 'active',
    ])->assertRedirect();

    expect(NumberingSeries::where('prefix', 'RC-')->exists())->toBeTrue();
});

it('persists general bill settings', function () {
    $this->actingAs($this->user)->put(route('society.billing.settings.general.update'), [
        'due_date_days' => 20,
        'grace_period_days' => 7,
        'calculation_method' => 'area_based',
        'minimum_bill_amount' => 200,
        'amount_decimal_places' => 2,
        'auto_sms_bill' => '1',
        // auto_email_bill omitted -> should become false
    ])->assertRedirect(route('society.billing.settings.general'));

    $setting = BillSetting::first();
    expect($setting->due_date_days)->toBe(20)
        ->and($setting->calculation_method)->toBe('area_based')
        ->and($setting->auto_sms_bill)->toBeTrue()
        ->and($setting->auto_email_bill)->toBeFalse();
});

it('persists late fee settings', function () {
    $this->actingAs($this->user)->put(route('society.billing.settings.late-fee.update'), [
        'grace_period_days' => 10,
        'late_fee_type' => 'flat',
        'late_fee_flat' => 250,
        'interest_calc_type' => 'compound',
        'apply_interest_after_days' => 45,
        'enable_late_fee' => '1',
    ])->assertRedirect(route('society.billing.settings.late-fee'));

    $setting = LateFeeSetting::first();
    expect($setting->grace_period_days)->toBe(10)
        ->and($setting->late_fee_type)->toBe('flat')
        ->and($setting->interest_calc_type)->toBe('compound');
});

it('persists bill design settings', function () {
    $this->actingAs($this->user)->put(route('society.billing.settings.design.update'), [
        'template' => 'classic',
        'society_name' => 'Sunrise Towers',
        'primary_color' => '#123456',
        'show_qr' => '1',
        // show_terms omitted -> false
    ])->assertRedirect(route('society.billing.settings.design'));

    $setting = BillSetting::first();
    expect($setting->template)->toBe('classic')
        ->and($setting->society_name)->toBe('Sunrise Towers')
        ->and($setting->show_qr)->toBeTrue()
        ->and($setting->show_terms)->toBeFalse();
});

it('generates and increments numbering series', function () {
    $series = NumberingSeries::where('document_type', 'maintenance_bill')->first();
    $before = $series->next_number;

    $number = $series->generateNext();

    expect($number)->toBe('MB-2025-'.str_pad((string) $before, 6, '0', STR_PAD_LEFT))
        ->and($series->fresh()->next_number)->toBe($before + 1);
});
