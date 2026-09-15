<?php

use App\Models\CollectionPayment;
use App\Models\FamilyMember;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Notice;
use App\Models\PaymentGatewayOrder;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\MemberPortalInvitation;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->society = Society::create(['name' => 'Green View', 'prefix' => 'GV', 'status' => 'active', 'subscription_status' => 'active', 'primary_email' => 'office@gv.test']);
    $this->otherSociety = Society::create(['name' => 'Blue Hills', 'prefix' => 'BH', 'status' => 'active', 'subscription_status' => 'active']);

    $this->admin = User::factory()->create(['name' => 'Office Admin']);
    linkSocietyAdmin($this->admin, $this->society);

    $this->member = Member::factory()->create(['society_id' => $this->society->id, 'name' => 'Asha Resident', 'flat_unit' => 'A-101', 'email' => 'asha@example.test', 'mobile' => '9111111111']);
    $this->unit = Unit::factory()->create(['society_id' => $this->society->id, 'unit_number' => 'A-101', 'status' => 'occupied', 'member_id' => $this->member->id]);

    $this->otherMember = Member::factory()->create(['society_id' => $this->society->id, 'name' => 'Other Resident', 'flat_unit' => 'B-202']);
    $this->foreignMember = Member::factory()->create(['society_id' => $this->otherSociety->id, 'name' => 'Foreign Resident']);
});

/**
 * Create + link a portal login for a member, the same way the invite action does.
 */
function portalUserFor(Member $member): User
{
    $user = User::factory()->create(['email' => $member->email ?? fake()->unique()->safeEmail(), 'name' => $member->name, 'password' => Hash::make('secret-123')]);
    linkSocietyUser($user, $member->society, 'member');
    $member->forceFill(['user_id' => $user->id, 'invited_at' => now()])->save();

    return $user;
}

it('lets the office invite a member to the portal and creates a member-role login', function () {
    Notification::fake();

    $this->actingAs($this->admin)->get(route('society.members.show', $this->member))
        ->assertOk()
        ->assertSee('Invite to portal');

    $this->actingAs($this->admin)->post(route('society.members.invite', $this->member))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->member->refresh();
    $user = $this->member->user;
    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('asha@example.test')
        ->and($user->society_id)->toBe($this->society->id)
        ->and($user->hasRole('member'))->toBeTrue()
        ->and($this->member->invited_at)->not->toBeNull();
    Notification::assertSentTo($user, MemberPortalInvitation::class);

    // Re-inviting reuses the same login instead of creating a duplicate.
    $this->actingAs($this->admin)->post(route('society.members.invite', $this->member))->assertRedirect();
    expect(User::where('email', 'asha@example.test')->count())->toBe(1)
        ->and($this->member->fresh()->user_id)->toBe($user->id);

    // A member without an email cannot be invited; a foreign member 404s.
    $noEmail = Member::factory()->create(['society_id' => $this->society->id, 'email' => null]);
    $this->actingAs($this->admin)->from(route('society.members.show', $noEmail))
        ->post(route('society.members.invite', $noEmail))
        ->assertSessionHasErrors('email');
    $this->actingAs($this->admin)->post(route('society.members.invite', $this->foreignMember))->assertNotFound();
});

it('redirects a member login to the portal and keeps members out of staff panels', function () {
    $user = portalUserFor($this->member);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret-123'])
        ->assertRedirect(route('member.dashboard'));

    $this->actingAs($user)->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('Welcome, Asha')
        ->assertSee('Green View');

    $this->actingAs($user)->get(route('society.dashboard'))->assertForbidden();
    $this->actingAs($user)->get(route('superadmin.dashboard'))->assertForbidden();
    $this->actingAs($this->admin)->get(route('member.dashboard'))->assertForbidden();
});

it('blocks the portal when the login is not linked to an active member of a live society', function () {
    $orphan = User::factory()->create();
    linkSocietyUser($orphan, $this->society, 'member');
    $this->actingAs($orphan)->get(route('member.dashboard'))->assertForbidden();

    $user = portalUserFor($this->member);
    $this->member->update(['status' => 'blocked']);
    $this->actingAs($user)->get(route('member.dashboard'))->assertForbidden();

    $this->member->update(['status' => 'active']);
    $this->society->update(['subscription_status' => 'expired']);
    $this->actingAs($user)->get(route('member.dashboard'))->assertForbidden();
});

it('shows only the member\'s own bills, lets them download a PDF and pay online', function () {
    Notification::fake();
    $user = portalUserFor($this->member);

    $mine = MaintenanceBill::factory()->create([
        'society_id' => $this->society->id, 'member_id' => $this->member->id, 'unit_id' => $this->unit->id, 'member_name' => $this->member->name,
        'bill_number' => 'MB-MINE-1', 'bill_month' => 'May 2026', 'total_amount' => 3000, 'collected_amount' => 0, 'outstanding_amount' => 3000, 'status' => 'pending',
    ]);
    $theirs = MaintenanceBill::factory()->create([
        'society_id' => $this->society->id, 'member_id' => $this->otherMember->id, 'member_name' => $this->otherMember->name,
        'bill_number' => 'MB-THEIRS-1', 'total_amount' => 3000, 'collected_amount' => 0, 'outstanding_amount' => 3000, 'status' => 'pending',
    ]);

    $this->actingAs($user)->get(route('member.bills.index'))
        ->assertOk()
        ->assertSee('MB-MINE-1')
        ->assertDontSee('MB-THEIRS-1');

    $this->actingAs($user)->get(route('member.bills.show', $mine))->assertOk()->assertSee('MB-MINE-1');
    $this->actingAs($user)->get(route('member.bills.show', $theirs))->assertNotFound();
    $this->actingAs($user)->get(route('member.bills.pdf', $theirs))->assertNotFound();

    $pdf = $this->actingAs($user)->get(route('member.bills.pdf', $mine));
    $pdf->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('application/pdf');

    $this->actingAs($user)->post(route('member.bills.pay', $theirs))->assertNotFound();
    $this->actingAs($user)->post(route('member.bills.pay', $mine))->assertRedirect();

    $order = PaymentGatewayOrder::firstOrFail();
    expect($order->member_id)->toBe($this->member->id)
        ->and((float) $order->amount)->toBe(3000.0)
        ->and($order->created_by)->toBe($user->id);

    $this->actingAs($user)->get(route('member.bills.checkout', $order))
        ->assertOk()
        ->assertSee('Simulate successful payment');

    $this->postJson(route('webhooks.payments'), [
        'order_id' => $order->provider_order_id, 'payment_id' => 'pay_m1', 'status' => 'paid', 'amount' => 3000, 'method' => 'upi',
    ])->assertOk();

    $payment = $order->fresh()->payment;
    expect($payment)->not->toBeNull()
        ->and($mine->fresh()->status)->toBe('paid');

    $this->actingAs($user)->get(route('member.bills.checkout', $order))
        ->assertOk()
        ->assertSee('Payment received')
        ->assertSee(route('member.payments.show', $payment), false);

    // Another member of the same society cannot open this checkout.
    $other = portalUserFor($this->otherMember);
    $this->actingAs($other)->get(route('member.bills.checkout', $order))->assertNotFound();
});

it('lists the member\'s payments with receipt view and PDF', function () {
    $user = portalUserFor($this->member);

    $mine = CollectionPayment::factory()->create(['society_id' => $this->society->id, 'member_id' => $this->member->id, 'receipt_number' => 'RCPT-MINE', 'status' => 'paid', 'paid_amount' => 1200, 'payment_mode' => 'upi']);
    $theirs = CollectionPayment::factory()->create(['society_id' => $this->society->id, 'member_id' => $this->otherMember->id, 'receipt_number' => 'RCPT-THEIRS', 'status' => 'paid', 'paid_amount' => 900, 'payment_mode' => 'cash']);

    $this->actingAs($user)->get(route('member.payments.index'))
        ->assertOk()
        ->assertSee('RCPT-MINE')
        ->assertDontSee('RCPT-THEIRS');

    $this->actingAs($user)->get(route('member.payments.show', $mine))->assertOk()->assertSee('RCPT-MINE');
    $this->actingAs($user)->get(route('member.payments.show', $theirs))->assertNotFound();

    $pdf = $this->actingAs($user)->get(route('member.payments.pdf', $mine));
    $pdf->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('application/pdf');
    $this->actingAs($user)->get(route('member.payments.pdf', $theirs))->assertNotFound();
});

it('lets a member raise a complaint that reaches the office, and threads replies both ways', function () {
    Notification::fake();
    $user = portalUserFor($this->member);

    $this->actingAs($user)->get(route('member.support.create'))->assertOk()->assertSee('Raise a Complaint');

    $this->actingAs($user)->post(route('member.support.store'), [
        'category' => 'Electrical', 'priority' => 'high', 'subject' => 'No power in A-101',
        'description' => 'Main line tripped since morning.', 'location' => 'Meter room', 'preferred_contact' => 'Phone',
    ])->assertRedirect();

    $ticket = SupportTicket::firstOrFail();
    expect($ticket->society_id)->toBe($this->society->id)
        ->and($ticket->member_id)->toBe($this->member->id)
        ->and($ticket->raised_by_type)->toBe('member')
        ->and($ticket->raised_by_name)->toBe('Asha Resident')
        ->and($ticket->flat_no)->toBe('A-101')
        ->and($ticket->created_by)->toBe($user->id)
        ->and($ticket->status)->toBe('open');
    Notification::assertSentTo($this->admin, TicketCreated::class);

    // Office sees it under Priority Support and replies; the resident is notified.
    $this->actingAs($this->admin)->get(route('society.support.index'))->assertOk()->assertSee('No power in A-101');
    $this->actingAs($this->admin)->post(route('society.support.reply', $ticket), ['message' => 'Electrician on the way.', 'status' => 'in_progress'])->assertRedirect();
    Notification::assertSentTo($user, TicketReplied::class);
    expect($ticket->fresh()->status)->toBe('in_progress');

    // Resident sees the thread and replies; office is notified.
    $this->actingAs($user)->get(route('member.support.show', $ticket))
        ->assertOk()
        ->assertSee('Electrician on the way.');
    $this->actingAs($user)->post(route('member.support.reply', $ticket), ['message' => 'Thanks, waiting.'])->assertRedirect();
    Notification::assertSentTo($this->admin, TicketReplied::class);
    expect($ticket->fresh()->replies()->count())->toBe(2);

    // Replying to a resolved ticket re-opens it.
    $ticket->forceFill(['status' => 'resolved', 'resolved_at' => now()])->save();
    $this->actingAs($user)->post(route('member.support.reply', $ticket), ['message' => 'Still not fixed.'])->assertRedirect();
    expect($ticket->fresh()->status)->toBe('open');

    // Other residents cannot see it.
    $other = portalUserFor($this->otherMember);
    $this->actingAs($other)->get(route('member.support.show', $ticket))->assertNotFound();
    $this->actingAs($other)->get(route('member.support.index'))->assertOk()->assertDontSee('No power in A-101');
});

it('manages the member\'s own family members and vehicles', function () {
    $user = portalUserFor($this->member);
    $other = portalUserFor($this->otherMember);

    $this->actingAs($user)->post(route('member.family.store'), [
        'name' => 'Ravi Resident', 'relation' => 'Spouse', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'mobile' => '9222222222', 'is_resident' => 1,
    ])->assertRedirect(route('member.family.index'));

    $family = FamilyMember::firstOrFail();
    expect($family->member_id)->toBe($this->member->id)
        ->and($family->society_id)->toBe($this->society->id)
        ->and($family->is_resident)->toBeTrue();

    $this->actingAs($user)->get(route('member.family.index'))->assertOk()->assertSee('Ravi Resident');
    $this->actingAs($other)->get(route('member.family.index'))->assertOk()->assertDontSee('Ravi Resident');
    $this->actingAs($other)->get(route('member.family.edit', $family))->assertNotFound();
    $this->actingAs($other)->delete(route('member.family.destroy', $family))->assertNotFound();

    $this->actingAs($user)->put(route('member.family.update', $family), ['name' => 'Ravi R.', 'relation' => 'Spouse', 'is_resident' => 0])->assertRedirect();
    expect($family->fresh()->name)->toBe('Ravi R.')->and($family->fresh()->is_resident)->toBeFalse();

    $this->actingAs($user)->post(route('member.vehicles.store'), [
        'registration_no' => 'mh 12 ab 1234', 'vehicle_type' => 'car', 'make' => 'Honda', 'model' => 'City', 'unit_id' => $this->unit->id,
    ])->assertRedirect(route('member.vehicles.index'));

    $vehicle = Vehicle::firstOrFail();
    expect($vehicle->registration_no)->toBe('MH 12 AB 1234')
        ->and($vehicle->member_id)->toBe($this->member->id)
        ->and($vehicle->owner_name)->toBe('Asha Resident')
        ->and($vehicle->unit_id)->toBe($this->unit->id);

    // Duplicate plate inside the society is rejected, another society is fine.
    $this->actingAs($other)->post(route('member.vehicles.store'), ['registration_no' => 'MH 12 AB 1234', 'vehicle_type' => 'car'])
        ->assertSessionHasErrors('registration_no');
    $this->actingAs($other)->get(route('member.vehicles.edit', $vehicle))->assertNotFound();

    // A unit of another member cannot be linked.
    $foreignUnit = Unit::factory()->create(['society_id' => $this->otherSociety->id]);
    $this->actingAs($user)->put(route('member.vehicles.update', $vehicle), ['registration_no' => 'MH 12 AB 1234', 'vehicle_type' => 'bike', 'unit_id' => $foreignUnit->id])
        ->assertSessionHasErrors('unit_id');

    $this->actingAs($user)->put(route('member.vehicles.update', $vehicle), ['registration_no' => 'MH 12 AB 1234', 'vehicle_type' => 'bike'])->assertRedirect();
    expect($vehicle->fresh()->vehicle_type)->toBe('bike');

    $this->actingAs($user)->delete(route('member.vehicles.destroy', $vehicle))->assertRedirect();
    expect(Vehicle::count())->toBe(0);

    // The office sees household details on the member page.
    $this->actingAs($this->admin)->get(route('society.members.show', $this->member))->assertOk()->assertSee('Ravi R.');
});

it('lets a member update contact details and password, and see targeted notices', function () {
    $user = portalUserFor($this->member);

    $this->actingAs($user)->get(route('member.profile'))->assertOk()->assertSee('Asha Resident');

    $this->actingAs($user)->put(route('member.profile.update'), ['name' => 'Asha R.', 'mobile' => '9333333333', 'email' => 'asha.new@example.test'])
        ->assertRedirect(route('member.profile'));
    expect($this->member->fresh()->name)->toBe('Asha R.')
        ->and($this->member->fresh()->mobile)->toBe('9333333333')
        ->and($user->fresh()->email)->toBe('asha.new@example.test');

    $this->actingAs($user)->put(route('member.profile.update'), ['name' => 'X', 'email' => $this->admin->email])
        ->assertSessionHasErrors('email');

    $this->actingAs($user)->put(route('member.profile.password'), ['current_password' => 'wrong', 'new_password' => 'new-secret-1', 'new_password_confirmation' => 'new-secret-1'])
        ->assertSessionHasErrors('current_password');
    $this->actingAs($user)->put(route('member.profile.password'), ['current_password' => 'secret-123', 'new_password' => 'new-secret-1', 'new_password_confirmation' => 'new-secret-1'])
        ->assertRedirect(route('member.profile'));
    expect(Hash::check('new-secret-1', $user->fresh()->password))->toBeTrue();

    $forMembers = Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => $this->society->id, 'target_roles' => ['member'], 'title' => 'Water supply cut', 'require_acknowledgement' => true]);
    Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => $this->society->id, 'target_roles' => ['accountant'], 'title' => 'Audit prep']);
    Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => $this->otherSociety->id, 'target_roles' => null, 'title' => 'Blue Hills only']);

    $this->actingAs($user)->get(route('member.notices.index'))
        ->assertOk()
        ->assertSee('Water supply cut')
        ->assertDontSee('Audit prep')
        ->assertDontSee('Blue Hills only');
    $this->actingAs($user)->post(route('member.notices.acknowledge', $forMembers))->assertRedirect();
    expect($forMembers->acknowledgements()->where('user_id', $user->id)->exists())->toBeTrue();
});
