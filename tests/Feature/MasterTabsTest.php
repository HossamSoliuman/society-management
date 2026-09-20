<?php

use App\Models\PaymentMode;
use App\Models\SocietyType;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create();
    linkSuperAdmin($this->superAdmin);

    SocietyType::create(['name' => 'Co-operative Housing', 'status' => 'active']);
    UnitType::create(['name' => '2 BHK Flat', 'status' => 'active']);
    PaymentMode::create(['name' => 'UPI Transfer', 'status' => 'active']);
});

it('shows society types by default and links the other tabs', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('superadmin.masters.index'))
        ->assertSuccessful()
        ->assertSee('Co-operative Housing')
        ->assertDontSee('2 BHK Flat')
        ->assertDontSee('UPI Transfer')
        ->assertSee(route('superadmin.masters.index', ['tab' => 'unit-types']), false)
        ->assertSee(route('superadmin.masters.index', ['tab' => 'payment-modes']), false);
});

it('switches to the requested tab', function (string $tab, string $visible, string $hidden) {
    $this->actingAs($this->superAdmin)
        ->get(route('superadmin.masters.index', ['tab' => $tab]))
        ->assertSuccessful()
        ->assertSee($visible)
        ->assertDontSee($hidden);
})->with([
    'unit types' => ['unit-types', '2 BHK Flat', 'Co-operative Housing'],
    'payment modes' => ['payment-modes', 'UPI Transfer', 'Co-operative Housing'],
]);

it('falls back to society types for an unknown tab', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('superadmin.masters.index', ['tab' => 'nope']))
        ->assertSuccessful()
        ->assertSee('Co-operative Housing');
});

it('redirects back to the matching tab after creating a record', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('superadmin.masters.unit-types.store'), ['name' => 'Shop', 'status' => 'active'])
        ->assertRedirect(route('superadmin.masters.index', ['tab' => 'unit-types']));

    $this->actingAs($this->superAdmin)
        ->post(route('superadmin.masters.payment-modes.store'), ['name' => 'Cash', 'status' => 'active'])
        ->assertRedirect(route('superadmin.masters.index', ['tab' => 'payment-modes']));
});
