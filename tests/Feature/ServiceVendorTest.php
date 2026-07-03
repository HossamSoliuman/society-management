<?php

use App\Models\ServiceVendor;
use App\Models\Society;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Neha Patil']);
    $this->society = Society::create([
        'name' => 'Green Meadows Society',
        'prefix' => 'GMS',
        'status' => 'active',
    ]);
});

it('loads the vendor management list with stats and filters', function () {
    ServiceVendor::factory()->count(5)->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.vendors.index'))
        ->assertOk()
        ->assertSee('Vendor Management')
        ->assertSee('Total Vendors')
        ->assertSee('Pending Approval')
        ->assertSee('Vendors List');
});

it('filters vendors by category', function () {
    ServiceVendor::factory()->create(['society_id' => $this->society->id, 'category' => 'Electrical']);

    $this->actingAs($this->user)
        ->get(route('society.vendors.index', ['category' => 'Electrical']))
        ->assertOk();
});

it('loads the add vendor form', function () {
    $this->actingAs($this->user)->get(route('society.vendors.create'))
        ->assertOk()
        ->assertSee('Add New Vendor')
        ->assertSee('Basic Information')
        ->assertSee('Banking Information');
});

it('stores a vendor and redirects with a success message', function () {
    $this->actingAs($this->user)
        ->post(route('society.vendors.store'), [
            'name' => 'SK Electricals',
            'category' => 'Electrical',
            'contact_person' => 'Suresh Kumar',
            'phone' => '9876543210',
            'email' => 'suresh@skelectricals.com',
            'address' => '12 MG Road',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pin_code' => '411001',
            'status' => 'active',
        ])
        ->assertRedirect(route('society.vendors.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('service_vendors', [
        'name' => 'SK Electricals',
        'contact_person' => 'Suresh Kumar',
        'society_id' => $this->society->id,
        'approval_status' => 'pending',
    ]);
});

it('loads the edit form and updates a vendor', function () {
    $vendor = ServiceVendor::factory()->create([
        'society_id' => $this->society->id,
        'name' => 'Old Name',
    ]);

    $this->actingAs($this->user)->get(route('society.vendors.edit', $vendor))
        ->assertOk()
        ->assertSee('Edit Vendor');

    $this->actingAs($this->user)
        ->put(route('society.vendors.update', $vendor), [
            'name' => 'New Name',
            'category' => $vendor->category,
            'contact_person' => $vendor->contact_person,
            'phone' => $vendor->phone,
            'email' => $vendor->email,
            'address' => $vendor->address,
            'city' => $vendor->city,
            'state' => $vendor->state,
            'pin_code' => $vendor->pin_code,
            'status' => 'active',
        ])
        ->assertRedirect(route('society.vendors.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('service_vendors', [
        'id' => $vendor->id,
        'name' => 'New Name',
    ]);
});

it('deletes a vendor', function () {
    $vendor = ServiceVendor::factory()->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)
        ->delete(route('society.vendors.destroy', $vendor))
        ->assertRedirect(route('society.vendors.index'));

    $this->assertDatabaseMissing('service_vendors', ['id' => $vendor->id]);
});
