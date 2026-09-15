<?php

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BankStatementLine;
use App\Models\ChargeHead;
use App\Models\CollectionPayment;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Notice;
use App\Models\NumberingSeries;
use App\Models\PaymentGatewayOrder;
use App\Models\ServiceVendor;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\Tax;
use App\Models\Tender;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RouteInstance;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->societyA = Society::create(['name' => 'Society A', 'prefix' => 'SA', 'status' => 'active']);
    $this->societyB = Society::create(['name' => 'Society B', 'prefix' => 'SB', 'status' => 'active']);

    $this->adminA = User::factory()->create();
    linkSocietyAdmin($this->adminA, $this->societyA);

    $this->adminB = User::factory()->create();
    linkSocietyAdmin($this->adminB, $this->societyB);
});

/**
 * Build a row owned by society A for the given route parameter.
 */
function foreignRowFor(string $routeName, string $param, Society $society): mixed
{
    $sid = ['society_id' => $society->id];

    return match ($param) {
        'member' => Member::factory()->create($sid),
        'unit' => Unit::factory()->create($sid),
        'bill' => MaintenanceBill::factory()->create($sid),
        'chargeHead' => ChargeHead::factory()->create($sid),
        'tax' => Tax::factory()->create($sid),
        'series' => NumberingSeries::factory()->create($sid),
        'payment' => CollectionPayment::factory()->create($sid),
        'category' => match (true) {
            str_starts_with($routeName, 'society.assets.') => AssetCategory::factory()->create($sid),
            str_starts_with($routeName, 'society.documents.') => DocumentCategory::factory()->create($sid),
            default => ExpenseCategory::factory()->create($sid),
        },
        'vendor' => str_starts_with($routeName, 'society.expenses.')
            ? Vendor::factory()->create($sid)
            : ServiceVendor::factory()->create($sid),
        'expense' => Expense::factory()->create($sid + [
            'category_id' => ExpenseCategory::factory()->create($sid)->id,
            'vendor_id' => null,
        ]),
        'asset' => Asset::factory()->create($sid + ['category_id' => AssetCategory::factory()->create($sid)->id]),
        'request' => SupportTicket::factory()->create($sid),
        'document' => Document::factory()->create($sid + ['document_category_id' => null]),
        'tender' => Tender::factory()->create($sid),
        'notice' => Notice::factory()->create($sid + ['status' => 'published', 'publish_at' => now()->subDay(), 'expires_at' => null, 'target_roles' => null]),
        'teamUser' => tap(User::factory()->create($sid), fn (User $u) => $u->roles()->sync([seededRole('staff')->id])),
        'order' => PaymentGatewayOrder::create($sid + ['provider' => 'fake', 'provider_order_id' => 'iso_'.uniqid(), 'amount' => 100, 'status' => 'created']),
        'line' => BankStatementLine::create($sid + [
            'account_id' => Account::factory()->create($sid + ['type' => 'detail', 'is_bank' => true])->id,
            'import_batch' => 'iso', 'statement_date' => now(), 'debit' => 0, 'credit' => 10,
        ]),
        default => throw new RuntimeException("No fixture for route parameter {{$param}} on {$routeName}; add one to SocietyIsolationTest."),
    };
}

it('returns 404 on every society record route when the row belongs to another society', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (RouteInstance $route) => str_starts_with((string) $route->getName(), 'society.'))
        ->filter(fn (RouteInstance $route) => $route->parameterNames() !== [] && ! in_array($route->getName(), ['society.placeholder', 'society.reports.show'], true));

    expect($routes)->not->toBeEmpty();

    $failures = [];

    foreach ($routes as $route) {
        $params = [];
        foreach ($route->parameterNames() as $param) {
            $params[$param] = foreignRowFor($route->getName(), $param, $this->societyA);
        }

        $method = collect($route->methods())->first(fn ($m) => $m !== 'HEAD');
        $url = route($route->getName(), $params);

        $response = $this->actingAs($this->adminB)->call($method, $url);

        if ($response->status() !== 404) {
            $failures[] = "{$method} {$route->getName()} => {$response->status()}";
        }
    }

    expect($failures)->toBe([], 'Cross-society access leaked on: '.implode(', ', $failures));
});

it('still resolves own rows for the owning society', function () {
    $member = Member::factory()->create(['society_id' => $this->societyA->id]);

    $this->actingAs($this->adminA)->get(route('society.members.show', $member))->assertOk();
    $this->actingAs($this->adminB)->get(route('society.members.show', $member))->assertNotFound();
});

it('scopes the trial balance to the given society', function () {
    $groupA = AccountGroup::factory()->create(['society_id' => $this->societyA->id, 'name' => 'Assets']);
    $groupB = AccountGroup::factory()->create(['society_id' => $this->societyB->id, 'name' => 'Assets']);

    Account::factory()->create(['society_id' => $this->societyA->id, 'group_id' => $groupA->id, 'type' => 'detail', 'code' => '1001', 'opening_balance' => 500, 'balance' => 500]);
    Account::factory()->create(['society_id' => $this->societyB->id, 'group_id' => $groupB->id, 'type' => 'detail', 'code' => '1001', 'opening_balance' => 900, 'balance' => 900]);

    $tb = app(AccountingService::class)->trialBalance($this->societyB);

    expect($tb['rows'])->toHaveCount(1)
        ->and($tb['total_debit'])->toBe(900.0);
});

it('lets the scopeForSociety query builder filter rows', function () {
    Member::factory()->count(2)->create(['society_id' => $this->societyA->id]);
    Member::factory()->count(3)->create(['society_id' => $this->societyB->id]);

    expect(Member::forSociety($this->societyA)->count())->toBe(2)
        ->and(Member::forSociety($this->societyB->id)->count())->toBe(3);
});
