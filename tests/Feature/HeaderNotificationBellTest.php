<?php

use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->society = Society::create([
        'name' => 'Green Meadows Society',
        'prefix' => 'GMS',
        'status' => 'active',
    ]);

    $this->admin = User::factory()->create(['name' => 'Neha Patil']);
    linkSocietyAdmin($this->admin, $this->society);

    $this->superAdmin = User::factory()->create(['name' => 'Platform Admin']);
    linkSuperAdmin($this->superAdmin);
});

function bellTicket(Society $society): SupportTicket
{
    return SupportTicket::create([
        'society_id' => $society->id,
        'ticket_number' => 'TKT-'.fake()->unique()->numerify('####'),
        'category' => 'Maintenance',
        'priority' => 'high',
        'subject' => 'Water leakage',
        'raised_by_type' => 'member',
        'raised_by_name' => 'Rajesh Kumar',
        'description' => 'Leak in bathroom.',
        'status' => 'open',
    ]);
}

it('hides the badge when there are no unread notifications', function () {
    $this->actingAs($this->superAdmin)->get(route('superadmin.dashboard'))
        ->assertSuccessful()
        ->assertDontSee('notification-badge')
        ->assertSee("You're all caught up.", false);
});

it('shows the real unread count and latest items in the bell', function () {
    $ticket = bellTicket($this->society);
    $this->superAdmin->notify(new TicketCreated($ticket));
    $this->superAdmin->notify(new TicketCreated($ticket));

    $read = $this->superAdmin->notifications()->first();
    $read->markAsRead();

    $this->actingAs($this->superAdmin)->get(route('superadmin.dashboard'))
        ->assertSuccessful()
        ->assertSee('<span class="notification-badge">1</span>', false)
        ->assertSee("New ticket {$ticket->ticket_number}")
        ->assertSee(route('notifications.open', $this->superAdmin->unreadNotifications()->first()->id));
});

it('renders the bell on the society panel too', function () {
    $ticket = bellTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));

    $this->actingAs($this->admin)->get(route('society.dashboard'))
        ->assertSuccessful()
        ->assertSee('<span class="notification-badge">1</span>', false)
        ->assertSee(route('society.notices.index'));
});

it('marks a notification read and redirects to its target when opened', function () {
    $ticket = bellTicket($this->society);
    $this->superAdmin->notify(new TicketCreated($ticket));
    $notification = $this->superAdmin->unreadNotifications()->first();

    $this->actingAs($this->superAdmin)
        ->get(route('notifications.open', $notification->id))
        ->assertRedirect(route('superadmin.tickets.show', $ticket));

    expect($notification->fresh()->read_at)->not->toBeNull();
    expect($this->superAdmin->unreadNotifications()->count())->toBe(0);
});

it('refuses to open another user\'s notification', function () {
    $ticket = bellTicket($this->society);
    $this->admin->notify(new TicketCreated($ticket));
    $notification = $this->admin->unreadNotifications()->first();

    $this->actingAs($this->superAdmin)
        ->get(route('notifications.open', $notification->id))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks every unread notification as read at once', function () {
    $ticket = bellTicket($this->society);
    $this->superAdmin->notify(new TicketCreated($ticket));
    $this->superAdmin->notify(new TicketCreated($ticket));
    $this->admin->notify(new TicketCreated($ticket));

    $this->actingAs($this->superAdmin)
        ->from(route('superadmin.dashboard'))
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('superadmin.dashboard'));

    expect($this->superAdmin->unreadNotifications()->count())->toBe(0)
        ->and($this->admin->unreadNotifications()->count())->toBe(1);
});
