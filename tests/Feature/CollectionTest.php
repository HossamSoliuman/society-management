<?php

use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\NumberingSeries;
use App\Models\Society;
use App\Models\User;
use Database\Seeders\CollectionPaymentSeeder;
use Database\Seeders\Phase2SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Neha Patil']);
    $this->society = Society::create([
        'name' => 'Green Meadows Society',
        'prefix' => 'GMS',
        'status' => 'active',
    ]);
    $this->seed(Phase2SettingsSeeder::class);
});

/**
 * @return array<string, mixed>
 */
function paymentPayload(array $overrides = []): array
{
    return array_merge([
        'member_name' => 'Rahul Sharma',
        'flat_number' => 'A-101',
        'unit_label' => 'A-101, Building A, Wing A, 1st Floor, 2 BHK',
        'bill_type' => 'Maintenance',
        'bill_period' => 'May 2024',
        'due_date' => '2024-05-31',
        'receipt_date' => '2024-05-31',
        'total_due' => 2850,
        'paid_amount' => 2850,
        'discount' => 0,
        'fine_penalty' => 0,
        'payment_mode' => 'upi',
    ], $overrides);
}

it('loads the payment collection index', function () {
    CollectionPayment::factory()->count(3)->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.collections.index'))
        ->assertOk()
        ->assertSee('Collections')
        ->assertSee('Total Collected (This Month)')
        ->assertSee('Collection Overview');
});

it('shows the exact seeded demo rows on the collection index', function () {
    $this->seed(CollectionPaymentSeeder::class);

    $this->actingAs($this->user)->get(route('society.collections.index'))
        ->assertOk()
        ->assertSee('RCPT-2024-1256')
        ->assertSee('Rahul Sharma')
        ->assertSee('Showing 1 to 8 of 124 entries');
});

it('loads the record payment page with payment-mode tiles', function () {
    $this->actingAs($this->user)->get(route('society.collections.create'))
        ->assertOk()
        ->assertSee('Record Payment')
        ->assertSee('Payment Summary')
        ->assertSee('Net Banking');
});

it('loads the pending dues page with aging tabs', function () {
    $this->actingAs($this->user)->get(route('society.collections.pending-dues'))
        ->assertOk()
        ->assertSee('Pending Dues')
        ->assertSee('Total Outstanding')
        ->assertSee('Due Today')
        ->assertSee('Dues Aging Summary');
});

it('loads the payment receipts index', function () {
    CollectionPayment::factory()->create(['society_id' => $this->society->id, 'receipt_number' => 'RCPT-TEST-0001']);

    $this->actingAs($this->user)->get(route('society.collections.receipts.index'))
        ->assertOk()
        ->assertSee('Payment Receipts')
        ->assertSee('RCPT-TEST-0001');
});

it('stores a payment with a receipt number from the series', function () {
    $series = NumberingSeries::where('document_type', 'receipt')->first();
    $expectedNumber = $series->sampleNumber();

    $this->actingAs($this->user)->post(route('society.collections.store'), paymentPayload())
        ->assertRedirect(route('society.collections.index'));

    $payment = CollectionPayment::where('receipt_number', $expectedNumber)->first();

    expect($payment)->not->toBeNull()
        ->and((float) $payment->paid_amount)->toBe(2850.0)
        ->and((float) $payment->balance_due)->toBe(0.0)
        ->and($payment->status)->toBe('paid')
        ->and($payment->collected_by)->toBe('Neha Patil');
});

it('computes a partial status and balance when underpaid', function () {
    $this->actingAs($this->user)->post(route('society.collections.store'), paymentPayload([
        'paid_amount' => 1000,
    ]))->assertRedirect();

    $payment = CollectionPayment::latest('id')->first();

    expect((float) $payment->balance_due)->toBe(1850.0)
        ->and($payment->status)->toBe('partial');
});

it('allocates the payment to its maintenance bill', function () {
    $bill = MaintenanceBill::factory()->create([
        'society_id' => $this->society->id,
        'total_amount' => 2850,
        'collected_amount' => 0,
        'outstanding_amount' => 2850,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)->post(route('society.collections.store'), paymentPayload([
        'maintenance_bill_id' => $bill->id,
    ]))->assertRedirect();

    $bill->refresh();

    expect((float) $bill->collected_amount)->toBe(2850.0)
        ->and((float) $bill->outstanding_amount)->toBe(0.0)
        ->and($bill->status)->toBe('paid');
});

it('increments the receipt number on each store', function () {
    $this->actingAs($this->user)->post(route('society.collections.store'), paymentPayload());
    $this->actingAs($this->user)->post(route('society.collections.store'), paymentPayload());

    $numbers = CollectionPayment::orderBy('id')->pluck('receipt_number');

    expect($numbers)->toHaveCount(2)
        ->and($numbers[0])->not->toBe($numbers[1]);
});

it('redirects to the receipt with auto-print when saving and printing', function () {
    $this->actingAs($this->user)->post(route('society.collections.store'), paymentPayload([
        'print' => 1,
    ]))->assertRedirect();

    $payment = CollectionPayment::latest('id')->first();

    $this->actingAs($this->user)->get(route('society.collections.receipts.show', ['payment' => $payment, 'print' => 1]))
        ->assertOk();
});

it('renders the printable receipt template on the receipt show page', function () {
    $payment = CollectionPayment::factory()->create([
        'society_id' => $this->society->id,
        'receipt_number' => 'RCPT-2024-1256',
        'member_name' => 'Rahul Sharma',
        'paid_amount' => 2850,
        'total_due' => 2850,
        'balance_due' => 0,
        'status' => 'paid',
        'payment_mode' => 'upi',
    ]);

    $this->actingAs($this->user)->get(route('society.collections.receipts.show', $payment))
        ->assertOk()
        ->assertSee('RCPT-2024-1256')
        ->assertSee('PAYMENT RECEIPT')
        ->assertSee('Rupees Two Thousand Eight Hundred Fifty Only');
});

it('filters online payments to gateway transactions', function () {
    CollectionPayment::factory()->create(['society_id' => $this->society->id, 'receipt_number' => 'RCPT-ONLINE', 'is_online' => true]);
    // Offline row has no collected amount, so it also stays out of the (global) Recent Transactions rail.
    CollectionPayment::factory()->create(['society_id' => $this->society->id, 'receipt_number' => 'RCPT-OFFLINE', 'is_online' => false, 'paid_amount' => 0, 'status' => 'pending', 'payment_mode' => null]);

    $this->actingAs($this->user)->get(route('society.collections.online'))
        ->assertOk()
        ->assertSee('RCPT-ONLINE')
        ->assertDontSee('RCPT-OFFLINE');
});
