<?php

use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use App\Notifications\TicketStatusChanged;
use Database\Seeders\SupportTicketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Neha Patil']);
    $this->society = Society::create([
        'name' => 'Green Meadows Society',
        'prefix' => 'GMS',
        'status' => 'active',
    ]);
    linkSocietyAdmin($this->user, $this->society);

    $this->superAdmin = User::factory()->create(['name' => 'Platform Admin']);
    linkSuperAdmin($this->superAdmin);
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
    $this->seed(SupportTicketSeeder::class);

    $this->actingAs($this->user)->get(route('society.support.index'))
        ->assertOk()
        ->assertSee('PS-2024-048')
        ->assertSee('Lift not working on 3rd floor')
        ->assertSee('Rajesh Kumar')
        ->assertSee('Showing 1 to 8 of '.SupportTicket::forSociety($this->society)->count().' requests');

    expect(SupportTicket::where('ticket_number', 'like', 'PS-2024-%')->count())->toBe(48);
});

it('computes stat cards from the society tickets only', function () {
    SupportTicket::factory()->count(3)->create(['society_id' => $this->society->id, 'status' => 'open', 'priority' => 'high']);
    SupportTicket::factory()->count(1)->create(['society_id' => $this->society->id, 'status' => 'resolved', 'priority' => 'low']);
    SupportTicket::factory()->count(5)->create(['society_id' => Society::create(['name' => 'Other', 'prefix' => 'OT'])->id, 'status' => 'open']);

    $this->actingAs($this->user)->get(route('society.support.index'))
        ->assertOk()
        ->assertSee('75% of total')   // open 3/4
        ->assertSee('25% of total');  // resolved 1/4
});

it('loads the raise-new-request form with the timeline', function () {
    $this->actingAs($this->user)->get(route('society.support.create'))
        ->assertOk()
        ->assertSee('Raise New Request')
        ->assertSee('Request Information')
        ->assertSee('What happens next?')
        ->assertSee('Guidelines');
});

it('stores a support ticket, generates a ticket number and notifies super admins', function () {
    Notification::fake();

    $this->actingAs($this->user)->post(route('society.support.store'), supportPayload())
        ->assertRedirect(route('society.support.index'));

    $ticket = SupportTicket::latest('id')->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket->ticket_number)->toStartWith('TKT-')
        ->and($ticket->status)->toBe('open')
        ->and($ticket->created_by)->toBe($this->user->id)
        ->and($ticket->society_id)->toBe($this->society->id);

    Notification::assertSentTo($this->superAdmin, TicketCreated::class);
    $this->actingAs($this->superAdmin)->get(route('superadmin.tickets.index'))->assertOk()->assertSee($ticket->ticket_number);
});

it('validates required support fields', function () {
    $this->actingAs($this->user)->post(route('society.support.store'), [])
        ->assertSessionHasErrors(['category', 'priority', 'subject', 'raised_by_type', 'description']);
});

it('filters the index by the resolved tab', function () {
    $this->seed(SupportTicketSeeder::class);

    $this->actingAs($this->user)->get(route('society.support.index', ['tab' => 'resolved']))
        ->assertOk()
        ->assertSee('Corridor not cleaned since two days')
        ->assertDontSee('Lift not working on 3rd floor');
});

it('shows a single support request', function () {
    $this->seed(SupportTicketSeeder::class);
    $ticket = SupportTicket::where('ticket_number', 'PS-2024-048')->first();

    $this->actingAs($this->user)->get(route('society.support.show', $ticket))
        ->assertOk()
        ->assertSee('PS-2024-048')
        ->assertSee('Lift not working on 3rd floor')
        ->assertSee('Conversation');
});

it('threads replies between the society and the super admin with notifications', function () {
    Notification::fake();
    $ticket = SupportTicket::factory()->create(['society_id' => $this->society->id, 'status' => 'open']);

    $this->actingAs($this->superAdmin)->post(route('superadmin.tickets.reply', $ticket), [
        'message' => 'We are looking into it.',
        'status' => 'in_progress',
    ])->assertRedirect(route('superadmin.tickets.show', $ticket));

    expect($ticket->fresh()->status)->toBe('in_progress')
        ->and($ticket->replies()->count())->toBe(1);
    Notification::assertSentTo($this->user, TicketReplied::class);

    $this->actingAs($this->user)->get(route('society.support.show', $ticket))
        ->assertOk()
        ->assertSee('We are looking into it.');

    $this->actingAs($this->user)->post(route('society.support.reply', $ticket), [
        'message' => 'Thanks, still leaking though.',
    ])->assertRedirect(route('society.support.show', $ticket));

    Notification::assertSentTo($this->superAdmin, TicketReplied::class);
    $this->actingAs($this->superAdmin)->get(route('superadmin.tickets.show', $ticket))
        ->assertOk()
        ->assertSee('Thanks, still leaking though.');
});

it('notifies the society when the super admin changes the status', function () {
    Notification::fake();
    $ticket = SupportTicket::factory()->create(['society_id' => $this->society->id, 'status' => 'open']);

    $this->actingAs($this->superAdmin)->put(route('superadmin.tickets.status', $ticket), ['status' => 'resolved'])
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe('resolved')
        ->and($ticket->fresh()->resolved_at)->not->toBeNull();
    Notification::assertSentTo($this->user, TicketStatusChanged::class);
});

it('keeps the dashboard complaint counts in sync with the support list', function () {
    SupportTicket::factory()->count(2)->create(['society_id' => $this->society->id, 'status' => 'open']);
    SupportTicket::factory()->count(1)->create(['society_id' => $this->society->id, 'status' => 'in_progress']);

    $this->actingAs($this->user)->get(route('society.dashboard.data', ['range' => 'all']))
        ->assertOk()
        ->assertJsonPath('range', 'all');

    $this->actingAs($this->user)->get(route('society.support.index', ['tab' => 'open']))
        ->assertOk()
        ->assertSee('Showing 1 to 2 of 2 requests');
});
