<?php

use App\Contracts\SmsGateway;
use App\Jobs\DeliverAnnouncement;
use App\Models\Announcement;
use App\Models\Member;
use App\Models\Notice;
use App\Models\Society;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use App\Services\Sms\FakeSmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create();
    linkSuperAdmin($this->superAdmin);

    $this->societyA = Society::create(['name' => 'Society A', 'prefix' => 'SA', 'status' => 'active']);
    $this->societyB = Society::create(['name' => 'Society B', 'prefix' => 'SB', 'status' => 'active']);

    $this->adminA = User::factory()->create(['mobile' => '9000000001']);
    linkSocietyUser($this->adminA, $this->societyA, 'society_admin');
    $this->staffA = User::factory()->create(['mobile' => '9000000002']);
    linkSocietyUser($this->staffA, $this->societyA, 'staff');
    $this->adminB = User::factory()->create(['mobile' => '9000000003']);
    linkSocietyUser($this->adminB, $this->societyB, 'society_admin');
});

it('computes estimated recipients from targeted users instead of a literal', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)->post(route('superadmin.notification.announcements.store'), [
        'title' => 'Planned downtime', 'message' => 'Sunday 2am', 'recipient_type' => 'all_staff',
        'priority' => 'normal', 'delivery_channel' => 'in_app', 'send_type' => 'now',
        'society_id' => $this->societyA->id,
    ])->assertRedirect(route('superadmin.notification.announcements'));

    $announcement = Announcement::latest('id')->first();
    expect($announcement->estimated_recipients)->toBe(2)
        ->and($announcement->society_id)->toBe($this->societyA->id)
        ->and($announcement->status)->toBe('sent');
    Queue::assertPushed(DeliverAnnouncement::class, fn ($job) => $job->item->is($announcement));

    $this->actingAs($this->superAdmin)
        ->getJson(route('superadmin.notification.announcements.estimate', ['recipient_type' => 'all_staff']))
        ->assertOk()
        ->assertJson(['count' => 3]);
});

it('delivers an announcement in-app, by email and by SMS to targeted users only', function () {
    Notification::fake();
    $this->app->instance(SmsGateway::class, new FakeSmsGateway);

    $announcement = Announcement::create([
        'title' => 'Hello', 'message' => 'World', 'recipient_type' => 'custom', 'target_roles' => ['society_admin'],
        'society_id' => $this->societyA->id, 'priority' => 'normal', 'delivery_channel' => 'all',
        'send_type' => 'now', 'status' => 'sent', 'created_by' => $this->superAdmin->id,
    ]);

    (new DeliverAnnouncement($announcement))->handle();

    Notification::assertSentTo($this->adminA, AnnouncementPublished::class, function ($n, $channels) {
        return $channels === ['database', 'mail', 'sms'];
    });
    Notification::assertNotSentTo($this->staffA, AnnouncementPublished::class);
    Notification::assertNotSentTo($this->adminB, AnnouncementPublished::class);
    expect($announcement->fresh()->delivered_count)->toBe(1);
});

it('records in-app notifications and sends SMS through the gateway when not faked', function () {
    $sms = new FakeSmsGateway;
    $this->app->instance(SmsGateway::class, $sms);

    $announcement = Announcement::create([
        'title' => 'Hello', 'message' => 'World', 'recipient_type' => 'all_staff', 'target_roles' => null,
        'society_id' => $this->societyB->id, 'priority' => 'normal', 'delivery_channel' => 'all',
        'send_type' => 'now', 'status' => 'sent', 'created_by' => $this->superAdmin->id,
    ]);

    (new DeliverAnnouncement($announcement))->handle();

    expect($this->adminB->notifications()->count())->toBe(1)
        ->and($sms->sentTo('9000000003'))->toBeTrue()
        ->and($sms->sentTo('9000000001'))->toBeFalse();
});

it('dispatches scheduled announcements and notices when their time comes', function () {
    Queue::fake();

    $due = Announcement::create([
        'title' => 'Due', 'message' => 'x', 'recipient_type' => 'all_staff', 'priority' => 'normal',
        'delivery_channel' => 'in_app', 'send_type' => 'scheduled', 'scheduled_at' => now()->subMinute(),
        'status' => 'scheduled', 'created_by' => $this->superAdmin->id,
    ]);
    $future = Announcement::create([
        'title' => 'Future', 'message' => 'x', 'recipient_type' => 'all_staff', 'priority' => 'normal',
        'delivery_channel' => 'in_app', 'send_type' => 'scheduled', 'scheduled_at' => now()->addHour(),
        'status' => 'scheduled', 'created_by' => $this->superAdmin->id,
    ]);
    $notice = Notice::factory()->create(['status' => 'scheduled', 'publish_at' => now()->subMinute(), 'expires_at' => null]);

    $this->artisan('announcements:dispatch-scheduled')->assertSuccessful();

    expect($due->fresh()->status)->toBe('sent')
        ->and($future->fresh()->status)->toBe('scheduled')
        ->and($notice->fresh()->status)->toBe('published');
    Queue::assertPushed(DeliverAnnouncement::class, 2);
});

it('shows a society only global notices and those targeted at it, with acknowledgement', function () {
    Queue::fake();
    $global = Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => null, 'target_roles' => null, 'title' => 'Global notice', 'require_acknowledgement' => true]);
    $forA = Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => $this->societyA->id, 'target_roles' => null, 'title' => 'Only for A', 'require_acknowledgement' => false]);
    $forB = Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => $this->societyB->id, 'target_roles' => null, 'title' => 'Only for B', 'require_acknowledgement' => false]);
    Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => null, 'target_roles' => ['accountant'], 'title' => 'Accountants only']);
    Notice::factory()->create(['status' => 'draft', 'society_id' => null, 'target_roles' => null, 'title' => 'Draft notice']);

    $this->actingAs($this->adminA)->get(route('society.notices.index'))
        ->assertOk()
        ->assertSee('Global notice')
        ->assertSee('Only for A')
        ->assertDontSee('Only for B')
        ->assertDontSee('Accountants only')
        ->assertDontSee('Draft notice')
        ->assertSee('1 notice require your acknowledgement');

    $this->actingAs($this->adminA)->get(route('society.notices.show', $forB))->assertNotFound();

    $this->actingAs($this->adminA)->post(route('society.notices.acknowledge', $global))->assertRedirect();
    expect($global->acknowledgements()->where('user_id', $this->adminA->id)->exists())->toBeTrue();
    $this->actingAs($this->adminA)->get(route('society.notices.index'))->assertOk()->assertSee('Acknowledged');

    $this->actingAs($this->adminA)->get(route('society.dashboard'))->assertOk()->assertSee('Only for A')->assertDontSee('Only for B');
});

it('creates a notice targeted at one society and shows real status counts', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)->get(route('superadmin.notices.create'))->assertOk()->assertSee('Estimated recipients');

    $this->actingAs($this->superAdmin)->post(route('superadmin.notices.store'), [
        'title' => 'Water cut', 'notice_type' => 'maintenance', 'priority' => 'high', 'content' => '<p>Tomorrow</p>',
        'publish_at' => now()->subMinute()->format('Y-m-d\TH:i'), 'audience_type' => 'all_members',
        'society_id' => $this->societyA->id, 'status' => 'published', 'send_email' => 1,
    ])->assertRedirect(route('superadmin.notices.index'));

    $notice = Notice::where('title', 'Water cut')->firstOrFail();
    expect($notice->society_id)->toBe($this->societyA->id)
        ->and($notice->estimated_recipients)->toBe(2)
        ->and($notice->channels())->toBe(['database', 'mail']);
    Queue::assertPushed(DeliverAnnouncement::class);

    Notice::factory()->count(2)->create(['status' => 'draft']);
    $this->actingAs($this->superAdmin)->get(route('superadmin.notices.index'))
        ->assertOk()
        ->assertSee('Water cut');
    expect(Notice::where('status', 'draft')->count())->toBe(2);
});

it('opens an announcement notification on the announcement page instead of the empty notices list', function () {
    $announcement = Announcement::create([
        'title' => 'Lift maintenance', 'message' => "Lift B is down on Sunday.\nUse lift A.", 'recipient_type' => 'all_staff', 'target_roles' => null,
        'society_id' => $this->societyA->id, 'priority' => 'high', 'category' => 'Maintenance', 'delivery_channel' => 'in_app',
        'send_type' => 'now', 'status' => 'sent', 'sent_at' => now(), 'created_by' => $this->superAdmin->id,
    ]);

    (new DeliverAnnouncement($announcement))->handle();

    $notification = $this->adminA->notifications()->firstOrFail();
    expect($notification->data['url'])->toBe(route('society.announcements.show', $announcement));

    $this->actingAs($this->adminA)->get(route('notifications.open', $notification->id))
        ->assertRedirect(route('society.announcements.show', $announcement));
    expect($notification->fresh()->read_at)->not->toBeNull();

    $this->actingAs($this->adminA)->get(route('society.announcements.show', $announcement))
        ->assertOk()
        ->assertSee('Lift maintenance')
        ->assertSee('Lift B is down on Sunday.')
        ->assertSee('High priority')
        ->assertSee('Maintenance');

    $this->actingAs($this->adminB)->get(route('society.announcements.show', $announcement))->assertNotFound();
});

it('re-resolves announcement and notice notifications stored with the old list URL', function () {
    $announcement = Announcement::create([
        'title' => 'Old announcement', 'message' => 'Body', 'recipient_type' => 'all_staff', 'target_roles' => null,
        'society_id' => null, 'priority' => 'normal', 'delivery_channel' => 'in_app',
        'send_type' => 'now', 'status' => 'sent', 'sent_at' => now(), 'created_by' => $this->superAdmin->id,
    ]);
    $notice = Notice::factory()->create(['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'society_id' => null, 'target_roles' => null]);

    $legacy = fn (string $type, int $id) => $this->adminA->notifications()->create([
        'id' => (string) Str::uuid(), 'type' => AnnouncementPublished::class,
        'data' => ['type' => $type, 'title' => 'x', 'priority' => 'normal', 'item_id' => $id, 'url' => route('society.notices.index')],
    ]);

    $this->actingAs($this->adminA)->get(route('notifications.open', $legacy('announcement', $announcement->id)->id))
        ->assertRedirect(route('society.announcements.show', $announcement));
    $this->actingAs($this->adminA)->get(route('notifications.open', $legacy('notice', $notice->id)->id))
        ->assertRedirect(route('society.notices.show', $notice));
});

it('sends members to the portal announcement page', function () {
    $resident = User::factory()->create(['mobile' => '9000000009']);
    linkSocietyUser($resident, $this->societyA, 'member');
    Member::factory()->create(['society_id' => $this->societyA->id, 'user_id' => $resident->id]);

    $announcement = Announcement::create([
        'title' => 'Pool closed', 'message' => 'Cleaning week.', 'recipient_type' => 'all_members', 'target_roles' => ['member'],
        'society_id' => $this->societyA->id, 'priority' => 'urgent', 'delivery_channel' => 'in_app',
        'send_type' => 'now', 'status' => 'sent', 'sent_at' => now(), 'created_by' => $this->superAdmin->id,
    ]);

    (new DeliverAnnouncement($announcement))->handle();

    $notification = $resident->notifications()->firstOrFail();
    expect($notification->data['url'])->toBe(route('member.announcements.show', $announcement));

    $this->actingAs($resident)->get(route('member.announcements.show', $announcement))
        ->assertOk()
        ->assertSee('Pool closed')
        ->assertSee('Urgent priority');
    $this->actingAs($this->adminA)->get(route('society.announcements.show', $announcement))->assertNotFound();
});
