<?php

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Notifications\SocietyAdminInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->society = Society::create(['name' => 'Green Meadows', 'prefix' => 'GM', 'status' => 'active']);
});

function societyUserWithRole(Society $society, string $role): User
{
    $user = User::factory()->create();
    linkSocietyUser($user, $society, $role);

    return $user;
}

it('blocks staff from accounting but allows accountants', function () {
    $staff = societyUserWithRole($this->society, 'staff');
    $accountant = societyUserWithRole($this->society, 'accountant');

    $this->actingAs($staff)->get(route('society.accounting.index'))->assertForbidden();
    $this->actingAs($accountant)->get(route('society.accounting.index'))->assertOk();
});

it('enforces module permissions per role', function (string $role, string $routeName, int $status) {
    $user = societyUserWithRole($this->society, $role);

    $this->actingAs($user)->get(route($routeName))->assertStatus($status);
})->with([
    ['staff', 'society.billing.bills.index', 403],
    ['staff', 'society.support.index', 200],
    ['staff', 'society.members.index', 200],
    ['accountant', 'society.members.index', 403],
    ['accountant', 'society.expenses.index', 200],
    ['manager', 'society.accounting.index', 403],
    ['manager', 'society.assets.index', 200],
    ['manager', 'society.settings.users.index', 403],
    ['society_admin', 'society.settings.users.index', 200],
    ['society_admin', 'society.accounting.index', 200],
]);

it('lets every society role open the dashboard and profile', function (string $role) {
    $user = societyUserWithRole($this->society, $role);

    $this->actingAs($user)->get(route('society.dashboard'))->assertOk();
    $this->actingAs($user)->get(route('society.profile'))->assertOk();
})->with(['society_admin', 'manager', 'accountant', 'staff']);

it('lets a society admin invite a staff user who can then log in', function () {
    Notification::fake();
    $admin = societyUserWithRole($this->society, 'society_admin');
    $staffRole = Role::where('name', 'staff')->firstOrFail();

    $this->actingAs($admin)->get(route('society.settings.users.create'))->assertOk();

    $this->actingAs($admin)->post(route('society.settings.users.store'), [
        'name' => 'New Staff',
        'email' => 'staff@gm.test',
        'mobile' => '9999999999',
        'role_id' => $staffRole->id,
        'status' => 'active',
    ])->assertRedirect(route('society.settings.users.index'));

    $staff = User::where('email', 'staff@gm.test')->firstOrFail();
    expect($staff->society_id)->toBe($this->society->id)
        ->and($staff->hasRole('staff'))->toBeTrue();
    Notification::assertSentTo($staff, SocietyAdminInvitation::class);

    $this->actingAs($staff)->get(route('society.support.index'))->assertOk();
    $this->actingAs($staff)->get(route('society.accounting.index'))->assertForbidden();
});

it('lists, edits, toggles and re-invites society users', function () {
    Notification::fake();
    $admin = societyUserWithRole($this->society, 'society_admin');
    $staff = societyUserWithRole($this->society, 'staff');
    $managerRole = Role::where('name', 'manager')->firstOrFail();

    $this->actingAs($admin)->get(route('society.settings.users.index'))
        ->assertOk()
        ->assertSee($staff->name)
        ->assertSee('Permission Matrix');

    $this->actingAs($admin)->put(route('society.settings.users.update', $staff), [
        'name' => 'Promoted Staff',
        'email' => $staff->email,
        'role_id' => $managerRole->id,
        'status' => 'active',
    ])->assertRedirect(route('society.settings.users.index'));

    expect($staff->fresh()->hasRole('manager'))->toBeTrue()
        ->and($staff->fresh()->name)->toBe('Promoted Staff');

    $this->actingAs($admin)->put(route('society.settings.users.status', $staff))->assertRedirect();
    expect($staff->fresh()->status)->toBe('inactive');

    $this->actingAs($admin)->put(route('society.settings.users.status', $staff))->assertRedirect();
    $this->actingAs($admin)->post(route('society.settings.users.resend-invitation', $staff))->assertRedirect();
    Notification::assertSentTo($staff, SocietyAdminInvitation::class);
});

it('cannot manage users of another society', function () {
    $admin = societyUserWithRole($this->society, 'society_admin');
    $other = Society::create(['name' => 'Other', 'prefix' => 'OT', 'status' => 'active']);
    $foreign = societyUserWithRole($other, 'staff');

    $this->actingAs($admin)->get(route('society.settings.users.edit', $foreign))->assertNotFound();
    $this->actingAs($admin)->put(route('society.settings.users.status', $foreign))->assertNotFound();
});

it('prevents an admin from removing their own admin access', function () {
    $admin = societyUserWithRole($this->society, 'society_admin');
    $staffRole = Role::where('name', 'staff')->firstOrFail();

    $this->actingAs($admin)->from(route('society.settings.users.edit', $admin))
        ->put(route('society.settings.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => $staffRole->id,
            'status' => 'active',
        ])->assertSessionHasErrors('role_id');

    $this->actingAs($admin)->put(route('society.settings.users.status', $admin))->assertStatus(422);
});
