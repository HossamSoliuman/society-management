<?php

use App\Models\Society;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create();
    linkSuperAdmin($this->superAdmin);

    $this->plan = SubscriptionPlan::create([
        'name' => 'Standard', 'code' => 'STD', 'status' => 'active', 'billing_cycle' => 'yearly',
        'plan_duration' => '1_year', 'amount' => 12000, 'max_units' => 100, 'plan_type' => 'standard',
    ]);
    $this->plan->syncModules(['dashboard', 'reports']);
});

function validPlanPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Premium', 'code' => 'PRM', 'plan_type' => 'premium', 'amount' => 24000,
        'max_units' => 500, 'billing_cycle' => 'yearly', 'plan_duration' => '1_year',
        'trial_period_days' => 0, 'priority' => 3, 'status' => 'active', 'color' => '#E84B1E',
        'modules' => ['dashboard', 'user_management'],
    ], $overrides);
}

it('lists plans with working action links', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('superadmin.subscription.plans'))
        ->assertSuccessful()
        ->assertSee(route('superadmin.subscription.plans.show', $this->plan), false)
        ->assertSee(route('superadmin.subscription.plans.edit', $this->plan), false)
        ->assertSee(route('superadmin.subscription.plans.destroy', $this->plan), false);
});

it('shows a plan with its modules and subscriptions', function () {
    $society = Society::create(['name' => 'Harbor Heights', 'prefix' => 'HH', 'status' => 'active', 'flats_count' => 10]);
    app(SubscriptionService::class)->createForSociety($society, $this->plan, [
        'start_date' => Carbon::today(), 'end_date' => Carbon::today()->addYear(), 'billing_cycle' => 'yearly',
    ]);

    $this->actingAs($this->superAdmin)
        ->get(route('superadmin.subscription.plans.show', $this->plan))
        ->assertSuccessful()
        ->assertSee('Standard')
        ->assertSee('Harbor Heights')
        ->assertSee('2 / 10');
});

it('stores a plan together with its enabled modules', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('superadmin.subscription.plans.store'), validPlanPayload())
        ->assertRedirect(route('superadmin.subscription.plans'));

    $plan = SubscriptionPlan::where('code', 'PRM')->firstOrFail();
    expect($plan->max_units)->toBe(500)
        ->and($plan->enabledModuleKeys())->toBe(['dashboard', 'user_management'])
        ->and($plan->modules()->count())->toBe(count(SubscriptionPlan::MODULES));
});

it('renders the edit form pre-filled', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('superadmin.subscription.plans.edit', $this->plan))
        ->assertSuccessful()
        ->assertSee('value="STD"', false)
        ->assertSee('value="dashboard" class="module-toggle" checked', false)
        ->assertSee('value="reports" class="module-toggle" checked', false);
});

it('updates a plan and resyncs modules', function () {
    $this->actingAs($this->superAdmin)
        ->put(route('superadmin.subscription.plans.update', $this->plan), validPlanPayload([
            'name' => 'Standard Plus', 'code' => 'STD', 'modules' => ['reports'],
        ]))
        ->assertRedirect(route('superadmin.subscription.plans.show', $this->plan));

    $this->plan->refresh()->load('modules');
    expect($this->plan->name)->toBe('Standard Plus')
        ->and($this->plan->amount)->toEqual(24000)
        ->and($this->plan->enabledModuleKeys())->toBe(['reports']);
});

it('rejects a plan code already used by another plan', function () {
    SubscriptionPlan::create(['name' => 'Basic', 'code' => 'BSC', 'amount' => 5000]);

    $this->actingAs($this->superAdmin)
        ->from(route('superadmin.subscription.plans.edit', $this->plan))
        ->put(route('superadmin.subscription.plans.update', $this->plan), validPlanPayload(['code' => 'BSC']))
        ->assertRedirect(route('superadmin.subscription.plans.edit', $this->plan))
        ->assertSessionHasErrors('code');
});

it('rejects unknown module keys', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('superadmin.subscription.plans.store'), validPlanPayload(['modules' => ['hacking']]))
        ->assertSessionHasErrors('modules.0');
});

it('toggles plan status', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('superadmin.subscription.plans.toggle-status', $this->plan))
        ->assertRedirect();

    expect($this->plan->refresh()->status)->toBe('inactive');

    $this->actingAs($this->superAdmin)->post(route('superadmin.subscription.plans.toggle-status', $this->plan));

    expect($this->plan->refresh()->status)->toBe('active');
});

it('soft deletes a plan with no subscriptions', function () {
    $this->actingAs($this->superAdmin)
        ->delete(route('superadmin.subscription.plans.destroy', $this->plan))
        ->assertRedirect(route('superadmin.subscription.plans'))
        ->assertSessionHas('success');

    expect(SubscriptionPlan::find($this->plan->id))->toBeNull()
        ->and(SubscriptionPlan::withTrashed()->find($this->plan->id))->not->toBeNull();
});

it('refuses to delete a plan that has subscriptions', function () {
    $society = Society::create(['name' => 'Harbor Heights', 'prefix' => 'HH', 'status' => 'active', 'flats_count' => 10]);
    app(SubscriptionService::class)->createForSociety($society, $this->plan, [
        'start_date' => Carbon::today(), 'end_date' => Carbon::today()->addYear(), 'billing_cycle' => 'yearly',
    ]);

    $this->actingAs($this->superAdmin)
        ->delete(route('superadmin.subscription.plans.destroy', $this->plan))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(SubscriptionPlan::find($this->plan->id))->not->toBeNull();
});

it('blocks non super admins from plan management', function () {
    $society = Society::create(['name' => 'Harbor Heights', 'prefix' => 'HH', 'status' => 'active', 'flats_count' => 10]);
    $admin = User::factory()->create();
    linkSocietyAdmin($admin, $society);

    $this->actingAs($admin)
        ->get(route('superadmin.subscription.plans.edit', $this->plan))
        ->assertForbidden();
});
