<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Society;
use App\Models\User;
use Database\Seeders\AssetCategorySeeder;
use Database\Seeders\AssetSeeder;
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

function seedAssetDemo(): void
{
    test()->seed(AssetCategorySeeder::class);
    test()->seed(AssetSeeder::class);
}

/**
 * @return array<string, mixed>
 */
function assetPayload(array $overrides = []): array
{
    $category = AssetCategory::factory()->create(['society_id' => test()->society->id]);

    return array_merge([
        'name' => 'Passenger Lift - B',
        'asset_code' => 'AST9001',
        'category_id' => $category->id,
        'location' => 'Tower A',
        'purchase_date' => '2023-05-30',
        'purchase_cost' => 750000,
        'status' => 'in_use',
        'condition' => 'good',
    ], $overrides);
}

it('loads the assets index with stat cards and rail', function () {
    $this->actingAs($this->user)->get(route('society.assets.index'))
        ->assertOk()
        ->assertSee('Assets Management')
        ->assertSee('Total Assets')
        ->assertSee('Total Asset Value')
        ->assertSee('Asset Categories')
        ->assertSee('Quick Actions');
});

it('shows the exact seeded demo rows on the assets index', function () {
    seedAssetDemo();

    $this->actingAs($this->user)->get(route('society.assets.index'))
        ->assertOk()
        ->assertSee('AST0001')
        ->assertSee('Passenger Lift - A')
        ->assertSee('Diesel Generator 125 kVA')
        ->assertSee('Showing 1 to 8 of 128 assets');
});

it('loads the add-new-asset form with the live summary', function () {
    $this->actingAs($this->user)->get(route('society.assets.create'))
        ->assertOk()
        ->assertSee('Add New Asset')
        ->assertSee('1. Asset Information')
        ->assertSee('Asset Summary')
        ->assertSee('Quick Tips');
});

it('stores an asset and redirects to the index', function () {
    $this->actingAs($this->user)->post(route('society.assets.store'), assetPayload())
        ->assertRedirect(route('society.assets.index'));

    $asset = Asset::latest('id')->first();

    expect($asset)->not->toBeNull()
        ->and($asset->asset_code)->toBe('AST9001')
        ->and((float) $asset->purchase_cost)->toBe(750000.0)
        ->and($asset->society_id)->toBe($this->society->id);
});

it('validates required asset fields', function () {
    $this->actingAs($this->user)->post(route('society.assets.store'), [])
        ->assertSessionHasErrors(['name', 'asset_code', 'category_id', 'location', 'purchase_date', 'purchase_cost', 'status', 'condition']);
});

it('rejects a duplicate asset code', function () {
    seedAssetDemo();

    $this->actingAs($this->user)->post(route('society.assets.store'), assetPayload(['asset_code' => 'AST0001']))
        ->assertSessionHasErrors('asset_code');
});

it('filters the index by status', function () {
    seedAssetDemo();

    $this->actingAs($this->user)->get(route('society.assets.index', ['status' => 'under_maintenance']))
        ->assertOk()
        ->assertSee('Booster Water Pump');
});

it('loads the asset categories index with counts', function () {
    seedAssetDemo();

    $this->actingAs($this->user)->get(route('society.assets.categories.index'))
        ->assertOk()
        ->assertSee('Asset Categories')
        ->assertSee('Lift')
        ->assertSee('Category Overview')
        ->assertSee('Showing 1 to 10 of 15 categories');
});

it('loads the add-new-category form with a preview', function () {
    $this->actingAs($this->user)->get(route('society.assets.categories.create'))
        ->assertOk()
        ->assertSee('Add New Asset Category')
        ->assertSee('Category Information')
        ->assertSee('Category Settings')
        ->assertSee('Icon Preview');
});

it('stores an asset category', function () {
    $this->actingAs($this->user)->post(route('society.assets.categories.store'), [
        'name' => 'Solar Panels',
        'code' => 'solar',
        'icon' => 'fa-bolt',
        'status' => 'active',
        'display_order' => 5,
        'movable' => '1',
    ])->assertRedirect(route('society.assets.categories.index'));

    $category = AssetCategory::where('name', 'Solar Panels')->first();

    expect($category)->not->toBeNull()
        ->and($category->code)->toBe('SOLAR')
        ->and($category->color)->toBe('yellow')
        ->and($category->movable)->toBeTrue()
        ->and($category->immovable)->toBeFalse();
});

it('requires at least one applicable asset type on a category', function () {
    $this->actingAs($this->user)->post(route('society.assets.categories.store'), [
        'name' => 'Solar Panels',
        'code' => 'SOLAR',
        'status' => 'active',
    ])->assertSessionHasErrors('movable');
});

it('loads the edit forms for asset and category', function () {
    $category = AssetCategory::factory()->create(['society_id' => $this->society->id]);
    $asset = Asset::factory()->create([
        'society_id' => $this->society->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($this->user)->get(route('society.assets.edit', $asset))
        ->assertOk()->assertSee('Edit Asset')->assertSee('Update Asset');
    $this->actingAs($this->user)->get(route('society.assets.categories.edit', $category))
        ->assertOk()->assertSee('Edit Asset Category')->assertSee('Update Category');
});
