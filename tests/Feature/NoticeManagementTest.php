<?php

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('loads the notice management list', function () {
    Notice::factory()->count(4)->create();

    $this->actingAs($this->user)->get(route('superadmin.notices.index'))
        ->assertOk()
        ->assertSee('Notice Management')
        ->assertSee('Total Notices');
});

it('loads the create notice form', function () {
    $this->actingAs($this->user)->get(route('superadmin.notices.create'))
        ->assertOk()
        ->assertSee('Create New Notice');
});

it('stores a notice and redirects', function () {
    $this->actingAs($this->user)
        ->post(route('superadmin.notices.store'), [
            'title' => 'Annual General Meeting 2025',
            'notice_type' => 'general',
            'priority' => 'high',
            'content' => 'AGM will be held on 15th June 2025 at 6:00 PM.',
            'publish_at' => '2025-05-30 10:30:00',
            'audience_type' => 'all_members',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('notices', [
        'title' => 'Annual General Meeting 2025',
    ]);
});
