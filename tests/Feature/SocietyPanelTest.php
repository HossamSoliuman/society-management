<?php

use App\Models\BillUpload;
use App\Models\Member;
use App\Models\Society;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->society = Society::create([
        'name' => 'Green Meadows Residency',
        'prefix' => 'GMR',
        'status' => 'active',
        'amenities' => ['Club House', 'Gymnasium'],
        'about' => "Paragraph one.\n\nParagraph two.",
    ]);

    Member::factory()->count(5)->active()->create(['society_id' => $this->society->id]);
    Member::factory()->count(2)->inactive()->create(['society_id' => $this->society->id]);
    Member::factory()->count(1)->blocked()->create(['society_id' => $this->society->id]);

    Unit::factory()->count(4)->occupied()->create(['society_id' => $this->society->id]);
    Unit::factory()->count(2)->vacant()->create(['society_id' => $this->society->id]);
    Unit::factory()->count(1)->underMaintenance()->create(['society_id' => $this->society->id]);
});

it('loads each society index page', function (string $route) {
    $this->actingAs($this->user)->get(route($route))->assertOk();
})->with([
    'society.dashboard',
    'society.profile',
    'society.profile.edit',
    'society.members.index',
    'society.members.create',
    'society.units.index',
    'society.units.create',
    'society.units.import',
    'society.billing.bulk-upload',
]);

it('renders the dashboard key figures', function () {
    $this->actingAs($this->user)->get(route('society.dashboard'))
        ->assertOk()
        ->assertSee('Society Dashboard')
        ->assertSee('2,45,800')   // Indian-grouped monthly collections
        ->assertSee('14,67,200')  // YTD revenue
        ->assertSee('Green Meadows CHS')
        ->assertSee('Quick Actions');
});

it('renders the profile with seeded values', function () {
    $this->society->update(['society_code' => 'GMR/2024/001', 'building_type' => 'Apartment']);

    $this->actingAs($this->user)->get(route('society.profile'))
        ->assertOk()
        ->assertSee('Green Meadows Residency')
        ->assertSee('GMR/2024/001')
        ->assertSee('Contact Information')
        ->assertSee('Amenities');
});

it('renders members table with colored stat cards', function () {
    $this->actingAs($this->user)->get(route('society.members.index'))
        ->assertOk()
        ->assertSee('Member Management')
        ->assertSee('is-success', false)
        ->assertSee('Total Members');
});

it('renders the bulk upload wizard and seeded file row', function () {
    BillUpload::create([
        'society_id' => $this->society->id,
        'original_name' => 'maintenance_bills_june2025.xlsx',
        'uploaded_by' => 'Super Admin',
        'records_count' => 156,
        'status' => 'validated',
    ]);

    $this->actingAs($this->user)->get(route('society.billing.bulk-upload'))
        ->assertOk()
        ->assertSee('Bulk Upload Bills')
        ->assertSee('Upload Excel File')
        ->assertSee('Required Columns')
        ->assertSee('maintenance_bills_june2025.xlsx')
        ->assertSee('Validated');
});

it('loads the placeholder page for stub links', function () {
    $this->actingAs($this->user)
        ->get(route('society.placeholder', ['page' => 'Tenant Management']))
        ->assertOk()
        ->assertSee('Tenant Management');
});

it('filters members by status', function () {
    $response = $this->actingAs($this->user)->get(route('society.members.index', ['status' => 'blocked']));

    $response->assertOk();
    expect($response->viewData('members')->total())->toBe(1);
});

it('filters units by status', function () {
    $response = $this->actingAs($this->user)->get(route('society.units.index', ['status' => 'vacant']));

    $response->assertOk();
    expect($response->viewData('units')->total())->toBe(2);
});

it('validates a new member', function () {
    $this->actingAs($this->user)
        ->post(route('society.members.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('stores a valid member', function () {
    $this->actingAs($this->user)->post(route('society.members.store'), [
        'name' => 'Test Member',
        'member_type' => 'owner',
        'flat_unit' => 'A-101',
        'tower_wing' => 'Tower A',
        'mobile' => '9876543210',
        'email' => 'test@example.com',
        'status' => 'active',
        'join_date' => '2024-01-15',
    ])->assertRedirect(route('society.members.index'));

    $this->assertDatabaseHas('members', ['name' => 'Test Member', 'flat_unit' => 'A-101']);
});

it('stores a valid unit', function () {
    $this->actingAs($this->user)->post(route('society.units.store'), [
        'unit_number' => 'Z-999',
        'building' => 'Building Z',
        'wing' => 'Wing Z',
        'floor' => '9th Floor',
        'unit_type' => '2 BHK',
        'area_sqft' => 950,
        'status' => 'vacant',
    ])->assertRedirect(route('society.units.index'));

    $this->assertDatabaseHas('units', ['unit_number' => 'Z-999']);
});

it('accepts a valid spreadsheet on bulk upload', function () {
    $file = UploadedFile::fake()->create(
        'bills.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );

    $this->actingAs($this->user)
        ->post(route('society.billing.bulk-upload.store'), ['file' => $file])
        ->assertRedirect(route('society.billing.bulk-upload'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('bill_uploads', ['original_name' => 'bills.xlsx', 'status' => 'validated']);
});

it('rejects a non-spreadsheet upload', function () {
    $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

    $this->actingAs($this->user)
        ->post(route('society.billing.bulk-upload.store'), ['file' => $file])
        ->assertSessionHasErrors('file');
});

it('rejects an oversized upload', function () {
    $file = UploadedFile::fake()->create(
        'big.xlsx', 6000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );

    $this->actingAs($this->user)
        ->post(route('society.billing.bulk-upload.store'), ['file' => $file])
        ->assertSessionHasErrors('file');
});

it('downloads a sample file', function () {
    $this->actingAs($this->user)
        ->get(route('society.billing.bulk-upload.sample'))
        ->assertOk();
});
