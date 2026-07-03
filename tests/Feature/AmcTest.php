<?php

use App\Models\AmcCategory;
use App\Models\AmcContract;
use App\Models\Society;
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

it('loads the AMC tracker list', function () {
    AmcContract::factory()->count(4)->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.amc.index'))
        ->assertOk()
        ->assertSee('AMC')
        ->assertSee('Total Contracts');
});

it('loads the add AMC form', function () {
    $this->actingAs($this->user)->get(route('society.amc.create'))
        ->assertOk();
});

it('loads the categories list', function () {
    AmcCategory::factory()->count(3)->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.amc.categories'))
        ->assertOk()
        ->assertSee('AMC Categories');
});

it('loads the add category form', function () {
    $this->actingAs($this->user)->get(route('society.amc.categories.create'))
        ->assertOk();
});

it('stores an AMC contract and redirects', function () {
    $category = AmcCategory::factory()->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)
        ->post(route('society.amc.store'), [
            'item_asset' => 'Elevator AMC',
            'amc_category_id' => $category->id,
            'vendor_name' => 'Kone India Pvt. Ltd.',
            'contract_type' => 'Comprehensive',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'amount' => 120000,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('amc_contracts', [
        'item_asset' => 'Elevator AMC',
        'society_id' => $this->society->id,
    ]);
});

it('stores an AMC category and redirects', function () {
    $this->actingAs($this->user)
        ->post(route('society.amc.categories.store'), [
            'name' => 'Elevator',
            'icon' => 'fa-elevator',
            'status' => 'active',
        ])
        ->assertRedirect(route('society.amc.categories'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('amc_categories', [
        'name' => 'Elevator',
        'society_id' => $this->society->id,
    ]);
});
