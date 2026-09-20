<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Society;
use App\Models\SocietyType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\SubscriptionRenewalReminder;
use App\Services\PlatformBillingService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create();
    linkSuperAdmin($this->superAdmin);

    $this->plan = SubscriptionPlan::create([
        'name' => 'Standard', 'code' => 'STD', 'status' => 'active', 'billing_cycle' => 'yearly',
        'plan_duration' => '1_year', 'amount' => 12000,
    ]);
    $this->premium = SubscriptionPlan::create([
        'name' => 'Premium', 'code' => 'PRM', 'status' => 'active', 'billing_cycle' => 'yearly',
        'plan_duration' => '1_year', 'amount' => 24000,
    ]);

    $this->society = Society::create([
        'name' => 'Harbor Heights', 'prefix' => 'HH', 'status' => 'active', 'grace_period_days' => 5,
        'flats_count' => 10,
    ]);
    $this->admin = User::factory()->create();
    linkSocietyAdmin($this->admin, $this->society);
});

function activeSubscription(Society $society, SubscriptionPlan $plan, ?Carbon $start = null, ?Carbon $end = null): Subscription
{
    return app(SubscriptionService::class)->createForSociety($society, $plan, [
        'start_date' => $start ?? Carbon::today()->subMonths(6),
        'end_date' => $end ?? Carbon::today()->addMonths(6),
        'billing_cycle' => 'yearly',
    ]);
}

it('syncs the society row when a subscription is created', function () {
    $subscription = activeSubscription($this->society, $this->plan);

    $society = $this->society->fresh();
    expect($subscription->subscription_number)->toStartWith('SUB-')
        ->and($society->subscription_plan_id)->toBe($this->plan->id)
        ->and($society->subscription_end_date->toDateString())->toBe($subscription->end_date->toDateString())
        ->and($society->subscription_status)->toBe('active');
});

it('creates the initial subscription row when a society is created', function () {
    Notification::fake();
    $type = SocietyType::create(['name' => 'Residential', 'status' => 'active']);

    $this->actingAs($this->superAdmin)->post(route('superadmin.societies.store'), [
        'name' => 'New Society', 'prefix' => 'NS', 'society_type_id' => $type->id,
        'flats_count' => 5, 'shops_count' => 0, 'offices_count' => 0,
        'address_line_1' => 'x', 'city' => 'c', 'state' => 's', 'pincode' => '1',
        'primary_email' => 'o@ns.test', 'primary_mobile' => '1',
        'subscription_plan_id' => $this->plan->id,
        'subscription_start_date' => now()->toDateString(),
        'subscription_end_date' => now()->addYear()->toDateString(),
        'billing_cycle' => 'yearly', 'grace_period_days' => 7, 'trial_period_days' => 0,
        'admin_name' => 'A', 'admin_email' => 'a@ns.test', 'admin_mobile' => '2',
    ])->assertRedirect();

    $society = Society::where('prefix', 'NS')->firstOrFail();
    expect($society->subscriptions()->count())->toBe(1)
        ->and($society->subscriptions()->first()->status)->toBe('active');
});

it('blocks the society once the grace period passes after refresh-status runs', function () {
    activeSubscription($this->society, $this->plan, Carbon::today()->subYear(), Carbon::today()->addDays(10));

    $this->actingAs($this->admin)->get(route('society.dashboard'))->assertOk();

    $this->travel(20)->days();
    $this->artisan('subscriptions:refresh-status')->assertSuccessful();

    expect($this->society->fresh()->subscription_status)->toBe('expired');
    $this->actingAs($this->admin)->get(route('society.dashboard'))->assertForbidden();
});

it('flags subscriptions as expiring soon within 30 days', function () {
    $subscription = activeSubscription($this->society, $this->plan, Carbon::today()->subMonths(11), Carbon::today()->addDays(20));

    expect($subscription->status)->toBe('expiring_soon')
        ->and($this->society->fresh()->subscription_status)->toBe('expiring_soon');

    $this->actingAs($this->admin)->get(route('society.dashboard'))->assertOk();
});

it('renders the superadmin renewals page with due counts', function () {
    activeSubscription($this->society, $this->plan, Carbon::today()->subMonths(11), Carbon::today()->addDays(20));

    $this->actingAs($this->superAdmin)->get(route('superadmin.subscription.renewals'))
        ->assertOk()
        ->assertViewHas('totalRenewals', 1)
        ->assertViewHas('dueIn30Days', 1)
        ->assertSee('Harbor Heights');
});

it('renews a subscription into a new linked row that extends access', function () {
    $old = activeSubscription($this->society, $this->plan, Carbon::today()->subYear(), Carbon::today()->subDays(2));
    $this->artisan('subscriptions:refresh-status');
    $this->actingAs($this->admin)->get(route('society.dashboard'))->assertOk(); // inside grace

    $this->actingAs($this->superAdmin)->get(route('superadmin.subscription.subscriptions.renew', $old))->assertOk();

    $this->actingAs($this->superAdmin)->post(route('superadmin.subscription.subscriptions.renew.store', $old), [
        'plan_id' => $this->plan->id,
        'start_date' => Carbon::today()->toDateString(),
        'end_date' => Carbon::today()->addYear()->toDateString(),
    ])->assertRedirect(route('superadmin.subscription.subscriptions'));

    $renewed = Subscription::where('renewed_from_id', $old->id)->firstOrFail();
    expect($renewed->status)->toBe('active')
        ->and($this->society->fresh()->subscription_end_date->toDateString())->toBe(Carbon::today()->addYear()->toDateString());

    $this->travel(30)->days();
    $this->actingAs($this->admin)->get(route('society.dashboard'))->assertOk();
});

it('upgrades mid-term by closing the old row and starting the new plan', function () {
    $old = activeSubscription($this->society, $this->plan);

    $new = app(SubscriptionService::class)->renew($old, $this->premium, ['start_date' => Carbon::today()->toDateString()]);

    expect($old->fresh()->status)->toBe('expired')
        ->and($old->fresh()->end_date->toDateString())->toBe(Carbon::yesterday()->toDateString())
        ->and($new->plan_id)->toBe($this->premium->id)
        ->and($this->society->fresh()->subscription_plan_id)->toBe($this->premium->id);
});

it('cancels a subscription and cascades to the society', function () {
    $subscription = activeSubscription($this->society, $this->plan);

    $this->actingAs($this->superAdmin)
        ->post(route('superadmin.subscription.subscriptions.cancel', $subscription), ['reason' => 'Non-payment'])
        ->assertRedirect();

    expect($subscription->fresh()->status)->toBe('cancelled')
        ->and($this->society->fresh()->subscription_status)->toBe('cancelled');
    $this->actingAs($this->admin)->get(route('society.dashboard'))->assertForbidden();
});

it('sends renewal reminders at 30, 7 and 1 days before expiry', function (int $days) {
    Notification::fake();
    activeSubscription($this->society, $this->plan, Carbon::today()->subMonths(11), Carbon::today()->addDays($days));

    $this->artisan('subscriptions:send-renewal-alerts')->assertSuccessful();

    Notification::assertSentTo($this->admin, SubscriptionRenewalReminder::class, fn ($n) => $n->daysLeft === $days);
})->with([30, 7, 1]);

it('does not send reminders on other days', function () {
    Notification::fake();
    activeSubscription($this->society, $this->plan, Carbon::today()->subMonths(11), Carbon::today()->addDays(15));

    $this->artisan('subscriptions:send-renewal-alerts');

    Notification::assertNothingSent();
});

it('round-trips the full society edit form and reactivates users', function () {
    $type = SocietyType::create(['name' => 'Residential', 'status' => 'active']);
    $this->society->update(['society_type_id' => $type->id, 'status' => 'inactive']);
    $this->admin->update(['status' => 'inactive']);

    $this->actingAs($this->superAdmin)->get(route('superadmin.societies.edit', $this->society))->assertOk()->assertSee('Officials');

    $this->actingAs($this->superAdmin)->put(route('superadmin.societies.update', $this->society), [
        'name' => 'Harbor Heights II', 'prefix' => 'HH', 'society_type_id' => $type->id,
        'city' => 'Alexandria', 'chairman_name' => 'Chair Person', 'grace_period_days' => 9,
        'auto_renewal' => 0, 'status' => 'active',
    ])->assertRedirect(route('superadmin.societies.show', $this->society));

    $society = $this->society->fresh();
    expect($society->name)->toBe('Harbor Heights II')
        ->and($society->city)->toBe('Alexandria')
        ->and($society->chairman_name)->toBe('Chair Person')
        ->and($society->grace_period_days)->toBe(9)
        ->and($society->auto_renewal)->toBeFalse()
        ->and($this->admin->fresh()->status)->toBe('active');
});

it('raises a platform invoice from a subscription and records payments and refunds', function () {
    $subscription = activeSubscription($this->society, $this->plan);

    $this->actingAs($this->superAdmin)->get(route('superadmin.billing.invoices.create'))->assertOk();
    $this->actingAs($this->superAdmin)->post(route('superadmin.billing.invoices.store'), [
        'subscription_id' => $subscription->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(15)->toDateString(),
        'tax_amount' => 0,
    ])->assertRedirect(route('superadmin.billing.invoices'));

    $invoice = Invoice::where('subscription_id', $subscription->id)->firstOrFail();
    expect($invoice->invoice_number)->toStartWith('INV-')
        ->and((float) $invoice->total_amount)->toBe(12000.0)
        ->and($invoice->status)->toBe('pending');

    $this->actingAs($this->superAdmin)->get(route('superadmin.billing.invoices'))->assertOk()->assertSee($invoice->invoice_number);

    $this->actingAs($this->superAdmin)->post(route('superadmin.billing.payments.store'), [
        'invoice_id' => $invoice->id, 'amount' => 5000, 'payment_method' => 'UPI',
        'payment_date' => now()->toDateString(), 'transaction_id' => 'UTR1',
    ])->assertRedirect(route('superadmin.billing.payments'));

    $invoice->refresh();
    expect((float) $invoice->paid_amount)->toBe(5000.0)
        ->and((float) $invoice->outstanding_amount)->toBe(7000.0)
        ->and($invoice->status)->toBe('partially_paid');

    $this->actingAs($this->superAdmin)->post(route('superadmin.billing.payments.store'), [
        'invoice_id' => $invoice->id, 'amount' => 7000, 'payment_method' => 'UPI', 'payment_date' => now()->toDateString(),
    ])->assertRedirect();
    expect($invoice->fresh()->status)->toBe('paid');

    $this->actingAs($this->superAdmin)->post(route('superadmin.billing.payments.store'), [
        'invoice_id' => $invoice->id, 'amount' => 1, 'payment_method' => 'UPI', 'payment_date' => now()->toDateString(),
    ])->assertSessionHasErrors('amount');

    $payment = Payment::where('invoice_id', $invoice->id)->first();
    $this->actingAs($this->superAdmin)->post(route('superadmin.billing.refunds.store'), [
        'payment_id' => $payment->id, 'amount' => 2000, 'refund_method' => 'Bank Transfer',
        'refund_date' => now()->toDateString(), 'status' => 'completed', 'reason' => 'Overcharged',
    ])->assertRedirect(route('superadmin.billing.refunds'));

    expect(Refund::count())->toBe(1)
        ->and($invoice->fresh()->status)->toBe('partially_paid')
        ->and((float) $invoice->fresh()->outstanding_amount)->toBe(2000.0);

    $this->actingAs($this->admin)->get(route('society.subscription.index'))
        ->assertOk()
        ->assertSee($invoice->invoice_number)
        ->assertSee('Standard');
});

it('marks pending invoices overdue after the due date', function () {
    $subscription = activeSubscription($this->society, $this->plan);
    $invoice = app(PlatformBillingService::class)->createInvoiceForSubscription($subscription, [
        'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(3)->toDateString(),
    ]);

    $this->artisan('invoices:mark-overdue');
    expect($invoice->fresh()->status)->toBe('pending');

    $this->travel(5)->days();
    $this->artisan('invoices:mark-overdue')->assertSuccessful();
    expect($invoice->fresh()->status)->toBe('overdue');

    $this->actingAs($this->superAdmin)->get(route('superadmin.billing.overdue'))->assertOk()->assertSee($invoice->invoice_number);
});

it('computes revenue by plan and by category from real payments', function () {
    $subscription = activeSubscription($this->society, $this->plan);
    $billing = app(PlatformBillingService::class);
    $invoice = $billing->createInvoiceForSubscription($subscription);
    $billing->recordPayment($invoice, ['amount' => 12000, 'payment_method' => 'UPI']);

    $byPlan = $billing->revenueByPlan();
    $byCategory = $billing->revenueByCategory();

    expect($byPlan)->toHaveCount(1)
        ->and($byPlan[0]['name'])->toBe('Standard')
        ->and($byPlan[0]['amount'])->toBe(12000.0)
        ->and($byPlan[0]['percentage'])->toBe(100.0)
        ->and($byCategory[0]['name'])->toBe('Standard');

    $this->actingAs($this->superAdmin)->get(route('superadmin.dashboard'))->assertOk()->assertSee('Standard');
    $this->actingAs($this->superAdmin)->get(route('superadmin.billing.overview'))->assertOk();
});
