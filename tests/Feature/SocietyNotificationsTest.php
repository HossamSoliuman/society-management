<?php

use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->society = Society::create([
        'name' => 'Green Meadows Society',
        'prefix' => 'GMS',
        'status' => 'active',
    ]);

    $this->admin = User::factory()->create(['name' => 'Neha Patil']);
    linkSocietyAdmin($this->admin, $this->society);

    $this->otherAdmin = User::factory()->create(['name' => 'Someone Else']);
    linkSocietyAdmin($this->otherAdmin, $this->society);
});

function inboxTicket(Society $society, string $subject = 'Water leakage'): SupportTicket
{
    return SupportTicket::create([
        'society_id' => $society->id,
        'ticket_number' => 'TKT-'.fake()->unique()->numerify('####'),
        'category' => 'Maintenance',
        'priority' => 'high',
        'subject' => $subject,
        'raised_by_type' => 'member',
        'raised_by_name' => 'Rajesh Kumar',
        'description' => 'Leak in bathroom.',
        'status' => 'open',
    ]);
}

/**
 * Titles rendered in the inbox list (ignores the header bell, which also shows unread items).
 *
 * @return list<string>
 */
function inboxTitles(TestResponse $response): array
{
    return collect($response->viewData('notifications')->items())->map(fn ($n) => $n->data['title'])->values()->all();
}

it('renders an empty inbox with zeroed stats', function () {
    $this->actingAs($this->admin)->get(route('society.notifications.index'))
        ->assertSuccessful()
        ->assertSee('No notifications yet')
        ->assertSee('Nothing to summarise yet.');
});

it('lists only the signed-in user\'s notifications with counts and type summary', function () {
    $ticket = inboxTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));
    $this->admin->notify(new TicketStatusChanged($ticket, 'open'));
    $this->admin->notifications()->latest()->first()->markAsRead();
    $this->otherAdmin->notify(new TicketCreated($ticket));

    $response = $this->actingAs($this->admin)->get(route('society.notifications.index'))
        ->assertSuccessful()
        ->assertSee("New ticket {$ticket->ticket_number}")
        ->assertSee("{$ticket->ticket_number} is now Open")
        ->assertSee('New Ticket')
        ->assertSee('Ticket Status');

    expect($response->viewData('stats'))->toMatchArray(['total' => 2, 'unread' => 1, 'read' => 1, 'today' => 2])
        ->and($response->viewData('byType'))->toBe(['ticket_created' => 1, 'ticket_status' => 1]);
});

it('filters by unread and read tabs', function () {
    $ticket = inboxTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));
    $this->admin->notify(new TicketStatusChanged($ticket, 'open'));
    $this->admin->notifications()->where('data->type', 'ticket_created')->first()->markAsRead();

    $unread = $this->actingAs($this->admin)->get(route('society.notifications.index', ['tab' => 'unread']))->assertSuccessful();
    expect(inboxTitles($unread))->toBe(["{$ticket->ticket_number} is now Open"]);

    $read = $this->actingAs($this->admin)->get(route('society.notifications.index', ['tab' => 'read']))->assertSuccessful();
    expect(inboxTitles($read))->toBe(["New ticket {$ticket->ticket_number}"]);
});

it('filters by type and searches the title', function () {
    $first = inboxTicket($this->society);
    $second = inboxTicket($this->society);
    $this->admin->notify(new TicketCreated($first));
    $this->admin->notify(new TicketStatusChanged($second, 'open'));

    $byType = $this->actingAs($this->admin)->get(route('society.notifications.index', ['type' => 'ticket_status']))->assertSuccessful();
    expect(inboxTitles($byType))->toBe(["{$second->ticket_number} is now Open"]);

    $bySearch = $this->actingAs($this->admin)->get(route('society.notifications.index', ['q' => $first->ticket_number]))->assertSuccessful();
    expect(inboxTitles($bySearch))->toBe(["New ticket {$first->ticket_number}"]);

    $this->actingAs($this->admin)->get(route('society.notifications.index', ['q' => 'nothing-matches']))
        ->assertSuccessful()
        ->assertSee('No notifications match your filters');
});

it('filters by date range', function () {
    $ticket = inboxTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));
    $this->admin->notifications()->update(['created_at' => now()->subDays(10)]);
    $this->admin->notify(new TicketStatusChanged($ticket, 'open'));

    $recent = $this->actingAs($this->admin)->get(route('society.notifications.index', ['from' => now()->subDay()->toDateString()]))->assertSuccessful();
    expect(inboxTitles($recent))->toBe(["{$ticket->ticket_number} is now Open"]);

    $older = $this->actingAs($this->admin)->get(route('society.notifications.index', ['to' => now()->subDays(5)->toDateString()]))->assertSuccessful();
    expect(inboxTitles($older))->toBe(["New ticket {$ticket->ticket_number}"]);
});

it('marks a single notification as read', function () {
    $ticket = inboxTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));
    $notification = $this->admin->unreadNotifications()->first();

    $this->actingAs($this->admin)
        ->from(route('society.notifications.index'))
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('society.notifications.index'));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('deletes a single notification', function () {
    $ticket = inboxTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));
    $notification = $this->admin->notifications()->first();

    $this->actingAs($this->admin)
        ->from(route('society.notifications.index'))
        ->delete(route('notifications.destroy', $notification->id))
        ->assertRedirect(route('society.notifications.index'));

    expect($this->admin->notifications()->count())->toBe(0);
});

it('refuses to read or delete another user\'s notification', function () {
    $ticket = inboxTicket($this->society);
    $this->otherAdmin->notify(new TicketCreated($ticket));
    $notification = $this->otherAdmin->notifications()->first();

    $this->actingAs($this->admin)->post(route('notifications.read', $notification->id))->assertNotFound();
    $this->actingAs($this->admin)->delete(route('notifications.destroy', $notification->id))->assertNotFound();

    expect($notification->fresh())->not->toBeNull()
        ->and($notification->fresh()->read_at)->toBeNull();
});

it('clears only the signed-in user\'s read notifications', function () {
    $ticket = inboxTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));
    $this->admin->notify(new TicketStatusChanged($ticket, 'open'));
    $this->admin->notifications()->where('data->type', 'ticket_created')->first()->markAsRead();
    $this->otherAdmin->notify(new TicketCreated($ticket));
    $this->otherAdmin->notifications()->update(['read_at' => now()]);

    $this->actingAs($this->admin)
        ->from(route('society.notifications.index'))
        ->delete(route('notifications.clear-read'))
        ->assertRedirect(route('society.notifications.index'))
        ->assertSessionHas('success', '1 read notification cleared.');

    expect($this->admin->notifications()->count())->toBe(1)
        ->and($this->admin->unreadNotifications()->count())->toBe(1)
        ->and($this->otherAdmin->notifications()->count())->toBe(1);
});

it('links the sidebar and bell to the inbox instead of the placeholder', function () {
    $this->actingAs($this->admin)->get(route('society.dashboard'))
        ->assertSuccessful()
        ->assertSee('href="'.route('society.notifications.index').'" class="nav-item', false)
        ->assertSee('View all notifications');
});
