<?php

use App\Models\ActivityLog;
use App\Models\Society;
use App\Models\SubscriptionPlan;
use App\Models\SystemLog;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['password' => 'secret-pass-1']);
    linkSuperAdmin($this->superAdmin);
    ActivityLog::query()->delete();
});

it('logs successful logins, failed logins and logouts', function () {
    $this->post('/login', ['email' => $this->superAdmin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->post('/login', ['email' => $this->superAdmin->email, 'password' => 'secret-pass-1'])->assertRedirect(route('superadmin.dashboard'));
    $this->post(route('logout'))->assertRedirect(route('login'));

    expect(ActivityLog::where('action', 'login_failed')->where('status', 'failed')->count())->toBe(1)
        ->and(ActivityLog::where('action', 'login')->where('user_id', $this->superAdmin->id)->count())->toBe(1)
        ->and(ActivityLog::where('action', 'logout')->count())->toBe(1);

    $this->actingAs($this->superAdmin)->get(route('superadmin.users.login-activity'))
        ->assertOk()
        ->assertSee($this->superAdmin->name);
});

it('records create, update and delete of audited models', function () {
    $this->actingAs($this->superAdmin);

    $society = Society::create(['name' => 'Audit Society', 'prefix' => 'AU', 'status' => 'active']);
    $society->update(['city' => 'Pune']);

    $plan = SubscriptionPlan::create(['name' => 'Std', 'code' => 'STD', 'status' => 'active', 'billing_cycle' => 'yearly', 'amount' => 100]);
    $subscription = app(SubscriptionService::class)->createForSociety($society, $plan, [
        'start_date' => now(), 'end_date' => now()->addYear(), 'billing_cycle' => 'yearly',
    ]);
    $subscription->delete();

    $created = ActivityLog::where('action', 'created')->where('subject_type', $society->getMorphClass())->first();
    $updated = ActivityLog::where('action', 'updated')->where('subject_id', $society->id)->where('subject_type', $society->getMorphClass())->first();

    expect($created)->not->toBeNull()
        ->and($created->society_id)->toBe($society->id)
        ->and($created->user_id)->toBe($this->superAdmin->id)
        ->and($updated->properties['city']['new'])->toBe('Pune')
        ->and(ActivityLog::where('module', 'Subscription')->where('action', 'created')->exists())->toBeTrue()
        ->and(ActivityLog::where('module', 'Subscription')->where('action', 'deleted')->exists())->toBeTrue();

    $this->actingAs($this->superAdmin)->get(route('superadmin.logs.audit-trail'))->assertOk()->assertSee('Audit Society');
});

it('writes a system log entry for server errors', function () {
    Route::get('/_boom', fn () => throw new RuntimeException('kaboom'))->middleware('web');

    $this->withoutExceptionHandling();
    try {
        $this->get('/_boom');
    } catch (RuntimeException) {
        // rethrown by withoutExceptionHandling; report() still ran below
    }

    $this->withExceptionHandling()->get('/_boom')->assertStatus(500);

    expect(SystemLog::where('message', 'like', '%kaboom%')->count())->toBeGreaterThanOrEqual(1);
    $this->actingAs($this->superAdmin)->get(route('superadmin.logs.system-logs'))->assertOk()->assertSee('kaboom');
});
