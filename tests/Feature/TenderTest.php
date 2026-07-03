<?php

use App\Models\Society;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->society = Society::create([
        'name' => 'Green Meadows Society',
        'prefix' => 'GMS',
        'status' => 'active',
    ]);
});

it('loads the active tenders list', function () {
    Tender::factory()->count(3)->create(['society_id' => $this->society->id, 'status' => 'open']);

    $this->actingAs($this->user)->get(route('society.tenders.active'))
        ->assertOk()
        ->assertSee('Tender Management')
        ->assertSee('Active Tenders');
});

it('loads the draft tenders list', function () {
    Tender::factory()->count(2)->create(['society_id' => $this->society->id, 'status' => 'draft']);

    $this->actingAs($this->user)->get(route('society.tenders.draft'))
        ->assertOk()
        ->assertSee('Draft Tenders');
});

it('loads the awarded tenders list', function () {
    $this->actingAs($this->user)->get(route('society.tenders.awarded'))
        ->assertOk()
        ->assertSee('Awarded Tenders');
});

it('loads the closed tenders list', function () {
    $this->actingAs($this->user)->get(route('society.tenders.closed'))
        ->assertOk()
        ->assertSee('Closed Tenders');
});

it('loads the create tender form', function () {
    $this->actingAs($this->user)->get(route('society.tenders.create'))
        ->assertOk()
        ->assertSee('Create New Tender')
        ->assertSee('Tender Information');
});

it('stores a tender and redirects', function () {
    $this->actingAs($this->user)
        ->post(route('society.tenders.store'), [
            'title' => 'Annual Cleaning Services',
            'tender_type' => 'service',
            'department' => 'Maintenance',
            'description' => 'Cleaning of common areas',
            'tender_category' => 'Open',
            'contact_person' => 'Ravi Sharma',
            'contact_email' => 'ravi@example.com',
            'contact_phone' => '9876543210',
            'action' => 'publish',
        ])
        ->assertRedirect(route('society.tenders.active'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tenders', [
        'title' => 'Annual Cleaning Services',
        'society_id' => $this->society->id,
        'status' => 'open',
    ]);
});
