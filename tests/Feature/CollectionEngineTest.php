<?php

use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\PaymentGatewayOrder;
use App\Models\Society;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\PaymentReceiptIssued;
use App\Services\PaymentAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Cashier One']);
    $this->society = Society::create(['name' => 'Green View', 'prefix' => 'GV', 'status' => 'active', 'primary_email' => 'office@gv.test']);
    linkSocietyAdmin($this->user, $this->society);

    $this->member = Member::factory()->create(['society_id' => $this->society->id, 'flat_unit' => 'A-101', 'email' => 'a101@example.test', 'mobile' => '9111111111']);
    $this->unit = Unit::factory()->create(['society_id' => $this->society->id, 'unit_number' => 'A-101', 'status' => 'occupied', 'member_id' => $this->member->id]);

    $this->older = MaintenanceBill::factory()->create([
        'society_id' => $this->society->id, 'member_id' => $this->member->id, 'unit_id' => $this->unit->id, 'member_name' => $this->member->name,
        'bill_month' => 'May 2026', 'bill_date' => '2026-05-01', 'due_date' => '2026-05-15',
        'total_amount' => 3000, 'collected_amount' => 0, 'outstanding_amount' => 3000, 'status' => 'overdue',
    ]);
    $this->newer = MaintenanceBill::factory()->create([
        'society_id' => $this->society->id, 'member_id' => $this->member->id, 'unit_id' => $this->unit->id, 'member_name' => $this->member->name,
        'bill_month' => 'June 2026', 'bill_date' => '2026-06-01', 'due_date' => '2026-06-15',
        'total_amount' => 3000, 'collected_amount' => 0, 'outstanding_amount' => 3000, 'status' => 'pending',
    ]);
});

it('allocates a member payment across bills oldest-first when no bill is chosen', function () {
    $service = app(PaymentAllocationService::class);
    $payment = $service->record($this->society, [
        'member_id' => $this->member->id,
        'bill_type' => 'Maintenance',
        'receipt_date' => now(),
        'paid_amount' => 5000,
        'payment_mode' => 'cash',
    ], 'Cashier One');

    expect($this->older->fresh()->status)->toBe('paid')
        ->and((float) $this->older->fresh()->outstanding_amount)->toBe(0.0)
        ->and($this->newer->fresh()->status)->toBe('partial')
        ->and((float) $this->newer->fresh()->outstanding_amount)->toBe(1000.0)
        ->and((float) $this->newer->fresh()->collected_amount)->toBe(2000.0)
        ->and((float) $payment->total_due)->toBe(6000.0)
        ->and((float) $payment->balance_due)->toBe(1000.0)
        ->and($payment->status)->toBe('partial')
        ->and($payment->collected_by)->toBe('Cashier One')
        ->and($payment->maintenance_bill_id)->toBe($this->older->id)
        ->and($service->lastAllocations)->toHaveCount(2);
});

it('records a payment through the form using the signed-in user as collector', function () {
    $this->actingAs($this->user)->post(route('society.collections.store'), [
        'member_id' => $this->member->id,
        'maintenance_bill_id' => $this->older->id,
        'bill_type' => 'Maintenance',
        'receipt_date' => now()->toDateString(),
        'total_due' => 3000,
        'paid_amount' => 3000,
        'payment_mode' => 'upi',
    ])->assertRedirect(route('society.collections.index'));

    $payment = CollectionPayment::latest('id')->first();
    expect($payment->collected_by)->toBe('Cashier One')
        ->and($payment->receipt_number)->toStartWith('RCPT-')
        ->and($payment->is_online)->toBeTrue()
        ->and($this->older->fresh()->status)->toBe('paid');

    $this->actingAs($this->user)->get(route('society.collections.index'))
        ->assertOk()
        ->assertSee('Cashier One')
        ->assertDontSee('Neha Patil');
});

it('shows real KPIs, pending dues buckets and aging on the collections pages', function () {
    $this->travelTo(Carbon::parse('2026-07-20'));
    $this->older->update(['due_date' => '2026-05-15']); // 66 days late → 61-90
    $this->newer->update(['due_date' => '2026-07-10', 'status' => 'overdue']); // 10 days late → 0-30

    $this->actingAs($this->user)->get(route('society.collections.pending-dues'))
        ->assertOk()
        ->assertSee('All Dues (2)')
        ->assertSee('0 - 30 Days (1)')
        ->assertSee('61 - 90 Days (1)')
        ->assertSee($this->member->name)
        ->assertSee('Dues Aging Summary');

    $this->actingAs($this->user)->get(route('society.collections.pending-dues', ['bucket' => '61-90']))
        ->assertOk()
        ->assertSee('May 2026')
        ->assertDontSee('June 2026');

    $this->actingAs($this->user)->get(route('society.collections.index'))
        ->assertOk()
        ->assertSee('1 Units / 1 Members');
});

it('downloads and emails a receipt PDF', function () {
    Notification::fake();
    $payment = app(PaymentAllocationService::class)->record($this->society, [
        'member_id' => $this->member->id, 'maintenance_bill_id' => $this->older->id,
        'bill_type' => 'Maintenance', 'paid_amount' => 3000, 'payment_mode' => 'cash',
    ], 'Cashier One');

    $response = $this->actingAs($this->user)->get(route('society.collections.receipts.pdf', $payment));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');

    $this->actingAs($this->user)->post(route('society.collections.receipts.email', $payment))->assertRedirect();
    Notification::assertSentOnDemand(PaymentReceiptIssued::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'a101@example.test');

    $this->actingAs($this->user)->get(route('society.collections.receipts.show', $payment))
        ->assertOk()
        ->assertSee('Green View')
        ->assertDontSee('Green Meadows Society');
});

it('creates a gateway order for a bill and completes it through the webhook', function () {
    Notification::fake();

    $this->actingAs($this->user)->post(route('society.collections.online.bill', $this->older))->assertRedirect();

    $order = PaymentGatewayOrder::firstOrFail();
    expect($order->provider)->toBe('fake')
        ->and((float) $order->amount)->toBe(3000.0)
        ->and($order->status)->toBe('created');

    $this->actingAs($this->user)->get(route('society.collections.online.checkout', $order))
        ->assertOk()
        ->assertSee('Simulate successful payment');

    // Webhook is unauthenticated + CSRF exempt.
    $this->postJson(route('webhooks.payments'), [
        'order_id' => $order->provider_order_id, 'payment_id' => 'pay_123', 'status' => 'paid', 'amount' => 3000, 'method' => 'upi',
    ])->assertOk()->assertJsonPath('status', 'paid');

    $order->refresh();
    $payment = $order->payment;
    expect($order->isPaid())->toBeTrue()
        ->and($payment)->not->toBeNull()
        ->and($payment->is_online)->toBeTrue()
        ->and($payment->payment_mode)->toBe('upi')
        ->and($payment->transaction_utr)->toBe('pay_123')
        ->and($this->older->fresh()->status)->toBe('paid');
    Notification::assertSentOnDemand(PaymentReceiptIssued::class);

    // Provider retry must not double-post.
    $this->postJson(route('webhooks.payments'), ['order_id' => $order->provider_order_id, 'status' => 'paid', 'amount' => 3000])->assertOk();
    expect(CollectionPayment::count())->toBe(1);

    $this->actingAs($this->user)->get(route('society.collections.online'))->assertOk()->assertSee($payment->receipt_number);
});

it('ignores webhooks for unknown orders and records failures', function () {
    $this->postJson(route('webhooks.payments'), ['order_id' => 'nope', 'status' => 'paid'])->assertOk()->assertJsonPath('order', null);

    $this->actingAs($this->user)->post(route('society.collections.online.bill', $this->newer));
    $order = PaymentGatewayOrder::firstOrFail();

    $this->postJson(route('webhooks.payments'), ['order_id' => $order->provider_order_id, 'status' => 'failed', 'payment_id' => 'pay_x'])->assertOk();
    expect($order->fresh()->status)->toBe('failed')
        ->and(CollectionPayment::count())->toBe(0);
});
