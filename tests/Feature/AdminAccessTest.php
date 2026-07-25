<?php

use App\Models\Member;
use App\Models\Role;
use App\Models\Society;
use App\Models\SocietyType;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\SocietyAdminInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'super_admin', 'display_name' => 'Super Admin', 'status' => 'active']);
    Role::create(['name' => 'society_admin', 'display_name' => 'Society Admin', 'status' => 'active']);
});

it('creates and invites a linked administrator with the society', function () {
    Notification::fake();
    $superAdmin = User::factory()->create();
    linkSuperAdmin($superAdmin);
    $type = SocietyType::create(['name' => 'Residential', 'status' => 'active']);
    $plan = SubscriptionPlan::create([
        'name' => 'Standard', 'code' => 'STD', 'status' => 'active', 'billing_cycle' => 'yearly',
    ]);

    $response = $this->actingAs($superAdmin)->post(route('superadmin.societies.store'), [
        'name' => 'Harbor Heights',
        'prefix' => 'HH',
        'society_type_id' => $type->id,
        'flats_count' => 20,
        'shops_count' => 0,
        'offices_count' => 0,
        'address_line_1' => '10 Harbor Road',
        'city' => 'Alexandria',
        'state' => 'Alexandria',
        'pincode' => '21500',
        'primary_email' => 'office@harbor.test',
        'primary_mobile' => '01000000000',
        'subscription_plan_id' => $plan->id,
        'subscription_start_date' => now()->toDateString(),
        'subscription_end_date' => now()->addYear()->toDateString(),
        'billing_cycle' => 'yearly',
        'grace_period_days' => 7,
        'trial_period_days' => 0,
        'admin_name' => 'Harbor Admin',
        'admin_email' => 'admin@harbor.test',
        'admin_mobile' => '01111111111',
    ]);

    $society = Society::where('prefix', 'HH')->firstOrFail();
    $admin = User::where('email', 'admin@harbor.test')->firstOrFail();

    $response->assertRedirect(route('superadmin.societies.show', $society));
    expect($admin->society_id)->toBe($society->id)
        ->and($admin->hasRole('society_admin'))->toBeTrue();
    Notification::assertSentTo($admin, SocietyAdminInvitation::class);
});

it('separates super-admin and society application areas', function () {
    $society = Society::create(['name' => 'Alpha', 'prefix' => 'ALP', 'status' => 'active']);
    $societyAdmin = User::factory()->create();
    linkSocietyAdmin($societyAdmin, $society);
    $superAdmin = User::factory()->create();
    linkSuperAdmin($superAdmin);

    $this->actingAs($societyAdmin)->get(route('superadmin.dashboard'))->assertForbidden();
    $this->actingAs($superAdmin)->get(route('society.placeholder', ['page' => 'test']))->assertForbidden();
});

it('rejects inactive users at login', function () {
    $user = User::factory()->create(['email' => 'inactive@example.test', 'status' => 'inactive']);
    linkSuperAdmin($user);
    $user->update(['status' => 'inactive']);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('prevents route binding records from crossing society boundaries', function () {
    $alpha = Society::create(['name' => 'Alpha', 'prefix' => 'ALP', 'status' => 'active']);
    $beta = Society::create(['name' => 'Beta', 'prefix' => 'BET', 'status' => 'active']);
    $admin = User::factory()->create();
    linkSocietyAdmin($admin, $alpha);
    $foreignMember = Member::factory()->create(['society_id' => $beta->id]);

    $this->actingAs($admin)->get(route('society.members.show', $foreignMember))->assertNotFound();
});

it('blocks a society after its subscription grace period', function () {
    $society = Society::create([
        'name' => 'Expired Society',
        'prefix' => 'EXP',
        'status' => 'active',
        'subscription_status' => 'active',
        'subscription_end_date' => now()->subDay(),
        'grace_period_days' => 0,
    ]);
    $admin = User::factory()->create();
    linkSocietyAdmin($admin, $society);

    $this->actingAs($admin)->get(route('society.placeholder', ['page' => 'test']))->assertForbidden();
});

it('allows an invited user to create a password once', function () {
    $user = User::factory()->create(['password' => str()->random(64)]);
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ])->assertRedirect(route('login'));

    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});
