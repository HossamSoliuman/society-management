<?php

use App\Models\Society;
use App\Models\SupportRequest;
use App\Models\User;
use Database\Seeders\SupportRequestSeeder;
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

/**
 * @return array<string, mixed>
 */
function supportPayload(array $overrides = []): array
{
    return array_merge([
        'category' => 'Maintenance',
        'priority' => 'high',
        'subject' => 'Water leakage in bathroom',
        'raised_by_type' => 'member',
        'raised_by_name' => 'Rajesh Kumar',
        'flat_no' => 'A-302',
        'description' => 'There is continuous water leakage from the ceiling in the master bathroom.',
    ], $overrides);
}

it('loads the priority support index with stat cards and rail', function () {
    $this->actingAs($this->user)->get(route('society.support.index'))
        ->assertOk()
        ->assertSee('Priority Support')
        ->assertSee('Total Requests')
        ->assertSee('Requests by Category')
        ->assertSee('Priority Distribution')
        ->assertSee('Quick Actions');
});

it('shows the exact seeded demo rows on the support index', function () {
    $this->seed(SupportRequestSeeder::class);

    $this->actingAs($this->user)->get(route('society.support.index'))
        ->assertOk()
        ->assertSee('PS-2024-048')
        ->assertSee('Lift not working on 3rd floor')
        ->assertSee('Rajesh Kumar')
        ->assertSee('Showing 1 to 8 of 48 requests');
});

it('loads the raise-new-request form with the timeline', function () {
    $this->actingAs($this->user)->get(route('society.support.create'))
        ->assertOk()
        ->assertSee('Raise New Request')
        ->assertSee('Request Information')
        ->assertSee('What happens next?')
        ->assertSee('Guidelines');
});

it('stores a support request and generates a request id', function () {
    $this->actingAs($this->user)->post(route('society.support.store'), supportPayload())
        ->assertRedirect(route('society.support.index'));

    $request = SupportRequest::latest('id')->first();

    expect($request)->not->toBeNull()
        ->and($request->request_id)->toStartWith('PS-')
        ->and($request->status)->toBe('open')
        ->and($request->society_id)->toBe($this->society->id);
});

it('validates required support fields', function () {
    $this->actingAs($this->user)->post(route('society.support.store'), [])
        ->assertSessionHasErrors(['category', 'priority', 'subject', 'raised_by_type', 'description']);
});

it('filters the index by the resolved tab', function () {
    $this->seed(SupportRequestSeeder::class);

    $this->actingAs($this->user)->get(route('society.support.index', ['tab' => 'resolved']))
        ->assertOk()
        ->assertSee('Corridor not cleaned since two days')
        ->assertDontSee('Lift not working on 3rd floor');
});

it('shows a single support request', function () {
    $this->seed(SupportRequestSeeder::class);
    $request = SupportRequest::where('request_id', 'PS-2024-048')->first();

    $this->actingAs($this->user)->get(route('society.support.show', $request))
        ->assertOk()
        ->assertSee('PS-2024-048')
        ->assertSee('Lift not working on 3rd floor');
});
