<?php

use App\Models\MaintenanceBill;
use App\Models\MaintenanceBillItem;
use App\Models\NumberingSeries;
use App\Models\Society;
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

/**
 * @return array<string, mixed>
 */
function billPayload(array $overrides = []): array
{
    return array_merge([
        'bill_month' => 'June 2025',
        'bill_date' => '30 May 2025',
        'due_date' => '15 Jun 2025',
        'billing_type' => 'Monthly Maintenance',
        'bill_cycle' => 'June 2025',
        'member_name' => 'Mr. Ramesh Sharma',
        'flat_number' => 'A-101',
        'tower_wing' => 'Tower A',
        'floor' => '1st Floor',
        'items' => [
            ['charge_head_name' => 'Maintenance Charges', 'description' => 'Monthly maintenance charges', 'amount' => 2500],
            ['charge_head_name' => 'Sinking Fund', 'description' => 'Sinking fund contribution', 'amount' => 500],
            ['charge_head_name' => 'Reserve Fund', 'description' => 'Reserve fund contribution', 'amount' => 300],
            ['charge_head_name' => 'Others', 'description' => 'Water tank cleaning charges', 'amount' => 200],
        ],
        'discount' => 0,
        'late_fee' => 0,
        'collection_account' => 'HDFC Bank - Current A/c',
    ], $overrides);
}

it('loads the bill list', function () {
    MaintenanceBill::factory()->count(3)->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.billing.bills.index'))
        ->assertOk()
        ->assertSee('Maintenance Bill List')
        ->assertSee('Total Bills')
        ->assertSee('Create New Bill');
});

it('loads the create bill page with default charge lines', function () {
    $this->actingAs($this->user)->get(route('society.billing.bills.create'))
        ->assertOk()
        ->assertSee('Create Maintenance Bill')
        ->assertSee('Bill Summary')
        ->assertSee('Maintenance Charges')
        ->assertSee('Save &amp; Generate Bill', false);
});

it('stores a bill with its items and assigns a bill number from the series', function () {
    $series = NumberingSeries::where('document_type', 'maintenance_bill')->first();
    $expectedNumber = $series->sampleNumber();

    $this->actingAs($this->user)->post(route('society.billing.bills.store'), billPayload())
        ->assertRedirect(route('society.billing.bills.index'));

    $bill = MaintenanceBill::where('bill_number', $expectedNumber)->first();

    expect($bill)->not->toBeNull()
        ->and($bill->items)->toHaveCount(4)
        ->and((float) $bill->sub_total)->toBe(3500.0)
        ->and((float) $bill->total_amount)->toBe(3500.0)
        ->and($bill->status)->toBe('pending');
});

it('applies discount and late fee to the total', function () {
    $this->actingAs($this->user)->post(route('society.billing.bills.store'), billPayload([
        'discount' => 200,
        'late_fee' => 100,
    ]))->assertRedirect();

    $bill = MaintenanceBill::latest('id')->first();

    expect((float) $bill->sub_total)->toBe(3500.0)
        ->and((float) $bill->total_amount)->toBe(3400.0)
        ->and((float) $bill->outstanding_amount)->toBe(3400.0);
});

it('increments the bill number on each store', function () {
    $this->actingAs($this->user)->post(route('society.billing.bills.store'), billPayload());
    $this->actingAs($this->user)->post(route('society.billing.bills.store'), billPayload());

    $numbers = MaintenanceBill::orderBy('id')->pluck('bill_number');

    expect($numbers)->toHaveCount(2)
        ->and($numbers[0])->not->toBe($numbers[1]);
});

it('requires a collection account', function () {
    $this->actingAs($this->user)->post(route('society.billing.bills.store'), billPayload([
        'collection_account' => '',
    ]))->assertSessionHasErrors('collection_account');
});

it('renders the shared bill template on the print view', function () {
    $bill = MaintenanceBill::factory()
        ->has(MaintenanceBillItem::factory()->count(4), 'items')
        ->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.billing.bills.print', $bill))
        ->assertOk()
        ->assertSee('MAINTENANCE BILL')
        ->assertSee($bill->bill_number);
});

it('renders the bill detail (show) page', function () {
    $bill = MaintenanceBill::factory()
        ->has(MaintenanceBillItem::factory()->count(4), 'items')
        ->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.billing.bills.show', $bill))
        ->assertOk()
        ->assertSee($bill->bill_number)
        ->assertSee('Print Bill');
});

it('loads the bulk upload page', function () {
    $this->actingAs($this->user)->get(route('society.billing.bills.bulk'))
        ->assertOk()
        ->assertSee('Bulk Upload Bills')
        ->assertSee('Sample Format');
});

it('filters bills by status', function () {
    MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'paid', 'bill_number' => 'MB-TEST-PAID']);
    MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'overdue', 'bill_number' => 'MB-TEST-OVERDUE']);

    $this->actingAs($this->user)->get(route('society.billing.bills.index', ['status' => 'paid']))
        ->assertOk()
        ->assertSee('MB-TEST-PAID')
        ->assertDontSee('MB-TEST-OVERDUE');
});
