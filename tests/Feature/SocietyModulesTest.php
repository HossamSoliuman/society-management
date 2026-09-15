<?php

use App\Models\AmcContract;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\CollectionPayment;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Expense;
use App\Models\ExpenseBudget;
use App\Models\ExpenseCategory;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\ServiceVendor;
use App\Models\Society;
use App\Models\Tender;
use App\Models\User;
use App\Notifications\AmcExpiryAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->society = Society::create(['name' => 'Modules Society', 'prefix' => 'MS', 'status' => 'active']);
    linkSocietyAdmin($this->user, $this->society);
});

it('computes expense KPIs, category donut and budget usage from data', function () {
    $utilities = ExpenseCategory::factory()->create(['society_id' => $this->society->id, 'name' => 'Utilities']);
    $salary = ExpenseCategory::factory()->create(['society_id' => $this->society->id, 'name' => 'Salary']);
    Expense::factory()->create(['society_id' => $this->society->id, 'category_id' => $utilities->id, 'vendor_id' => null, 'amount' => 3000, 'paid_amount' => 3000, 'due_amount' => 0, 'payment_status' => 'paid', 'expense_date' => now()]);
    Expense::factory()->create(['society_id' => $this->society->id, 'category_id' => $salary->id, 'vendor_id' => null, 'amount' => 7000, 'paid_amount' => 0, 'due_amount' => 7000, 'payment_status' => 'pending', 'expense_date' => now()]);
    ExpenseBudget::create(['society_id' => $this->society->id, 'expense_category_id' => $salary->id, 'year' => now()->year, 'amount' => 14000]);

    $this->actingAs($this->user)->get(route('society.expenses.index'))
        ->assertOk()
        ->assertSee('10,000')  // month total
        ->assertSee('7,000')   // pending
        ->assertSee('14,000')  // budget
        ->assertSee('71% Used')
        ->assertSee('Salary');

    $this->actingAs($this->user)->get(route('society.expenses.categories.index'))
        ->assertOk()
        ->assertSee('Salary')
        ->assertSee('50%', false);
});

it('saves a category budget from the category form', function () {
    $this->actingAs($this->user)->post(route('society.expenses.categories.store'), [
        'name' => 'Security', 'icon' => 'fa-tag', 'status' => 'active', 'applicable_for' => 'all_buildings',
        'budget_amount' => 120000, 'budget_year' => now()->year,
    ])->assertRedirect(route('society.expenses.categories.index'));

    $category = ExpenseCategory::where('name', 'Security')->firstOrFail();
    expect(ExpenseBudget::where('expense_category_id', $category->id)->value('amount'))->toBe('120000.00');
});

it('computes asset stats and imports assets from a spreadsheet', function () {
    $category = AssetCategory::factory()->create(['society_id' => $this->society->id, 'name' => 'Lifts']);
    Asset::factory()->count(2)->create(['society_id' => $this->society->id, 'category_id' => $category->id, 'status' => 'in_use', 'purchase_cost' => 1000, 'current_value' => 1000]);
    Asset::factory()->create(['society_id' => $this->society->id, 'category_id' => $category->id, 'status' => 'under_maintenance', 'purchase_cost' => 500, 'current_value' => null]);

    $this->actingAs($this->user)->get(route('society.assets.index'))
        ->assertOk()
        ->assertSee('66.67%')
        ->assertSee('2,500');

    $csv = implode("\n", [
        'Name,Category,Brand,Purchase Date,Purchase Cost,Status,Condition',
        'DG Set,Generators,Kirloskar,01/01/2024,90000,in_use,good',
        'Water Pump,Lifts,Crompton,,12000,under maintenance,fair',
        ',Lifts,,,,,',
    ]);
    $this->actingAs($this->user)->post(route('society.assets.import'), ['file' => UploadedFile::fake()->createWithContent('assets.csv', $csv)])
        ->assertRedirect(route('society.assets.index'));

    expect(Asset::where('name', 'DG Set')->exists())->toBeTrue()
        ->and(AssetCategory::where('name', 'Generators')->where('society_id', $this->society->id)->exists())->toBeTrue()
        ->and(Asset::where('name', 'Water Pump')->value('status'))->toBe('under_maintenance')
        ->and(Asset::count())->toBe(5);

    $this->actingAs($this->user)->get(route('society.assets.import.sample'))->assertOk();
});

it('refreshes AMC statuses and emails admins about expiring contracts', function () {
    Notification::fake();
    $expiring = AmcContract::factory()->create(['society_id' => $this->society->id, 'status' => 'active', 'end_date' => Carbon::today()->addDays(7), 'item_asset' => 'Lift AMC']);
    $expired = AmcContract::factory()->create(['society_id' => $this->society->id, 'status' => 'active', 'end_date' => Carbon::today()->subDay()]);
    $fine = AmcContract::factory()->create(['society_id' => $this->society->id, 'status' => 'active', 'end_date' => Carbon::today()->addDays(120)]);

    $this->artisan('amc:send-expiry-alerts')->assertSuccessful();

    expect($expiring->fresh()->status)->toBe('expiring_soon')
        ->and($expired->fresh()->status)->toBe('expired')
        ->and($fine->fresh()->status)->toBe('active');
    Notification::assertSentTo($this->user, AmcExpiryAlert::class, fn ($n) => $n->contract->is($expiring) && $n->daysLeft === 7);

    $this->actingAs($this->user)->get(route('society.amc.index'))->assertOk()->assertSee('Lift AMC');
});

it('shows real vendor stats', function () {
    ServiceVendor::factory()->count(2)->create(['society_id' => $this->society->id, 'status' => 'active', 'approval_status' => 'approved']);
    ServiceVendor::factory()->create(['society_id' => $this->society->id, 'status' => 'active', 'approval_status' => 'pending']);

    $this->actingAs($this->user)->get(route('society.vendors.index'))->assertOk()->assertSee('Vendor');
});

it('walks a tender through draft, publish, award and close', function () {
    $payload = [
        'title' => 'Lift AMC Tender', 'tender_type' => 'service', 'department' => 'Maintenance', 'description' => 'Annual lift maintenance',
        'tender_category' => 'Open', 'estimated_value' => 250000, 'start_date' => now()->toDateString(), 'submission_deadline' => now()->addDays(10)->format('Y-m-d\TH:i'),
        'contact_person' => 'Secretary', 'contact_email' => 'sec@ms.test', 'contact_phone' => '9999999999',
        'action' => 'draft',
    ];
    $this->actingAs($this->user)->post(route('society.tenders.store'), $payload)->assertRedirect(route('society.tenders.draft'));

    $tender = Tender::firstOrFail();
    expect($tender->status)->toBe('draft')->and($tender->reference_no)->toStartWith('TND-');

    $this->actingAs($this->user)->get(route('society.tenders.show', $tender))->assertOk()->assertSee('Publish');
    $this->actingAs($this->user)->get(route('society.tenders.edit', $tender))->assertOk()->assertSee('Lift AMC Tender');

    $this->actingAs($this->user)->put(route('society.tenders.update', $tender), ['title' => 'Lift AMC Tender 2026'] + $payload)->assertRedirect(route('society.tenders.show', $tender));
    expect($tender->fresh()->title)->toBe('Lift AMC Tender 2026');

    $this->actingAs($this->user)->post(route('society.tenders.publish', $tender))->assertRedirect();
    expect($tender->fresh()->status)->toBe('open');
    $this->actingAs($this->user)->get(route('society.tenders.active'))->assertOk()->assertSee('Lift AMC Tender 2026');

    $this->actingAs($this->user)->post(route('society.tenders.award', $tender), ['awarded_vendor' => 'Otis India', 'contract_value' => 240000])->assertRedirect();
    $tender->refresh();
    expect($tender->status)->toBe('awarded')->and($tender->awarded_vendor)->toBe('Otis India')->and((float) $tender->contract_value)->toBe(240000.0);
    $this->actingAs($this->user)->get(route('society.tenders.awarded'))->assertOk()->assertSee('Otis India')->assertSee('240,000');

    $this->actingAs($this->user)->post(route('society.tenders.close', $tender), ['closed_reason' => 'Completed'])->assertRedirect();
    expect($tender->fresh()->status)->toBe('closed');

    $this->actingAs($this->user)->get(route('society.tenders.reports'))->assertOk()->assertSee('Otis India')->assertSee('Tender Reports');
});

it('uploads, previews, downloads and deletes documents with category CRUD', function () {
    Storage::fake('local');
    $category = DocumentCategory::factory()->create(['society_id' => $this->society->id, 'name' => 'Minutes', 'status' => 'active']);

    $this->actingAs($this->user)->post(route('society.documents.store'), [
        'name' => 'AGM Minutes', 'document_category_id' => $category->id, 'type' => 'PDF', 'confidentiality' => 'general',
        'file' => UploadedFile::fake()->create('minutes.pdf', 120, 'application/pdf'),
    ])->assertRedirect(route('society.documents.index'));

    $document = Document::firstOrFail();
    Storage::disk('local')->assertExists($document->file_path);
    expect($document->uploaded_by)->toBe($this->user->name)->and($document->size)->toContain('KB');

    $this->actingAs($this->user)->get(route('society.documents.download', $document))->assertOk();
    $this->actingAs($this->user)->get(route('society.documents.preview', $document))->assertOk();
    expect($document->fresh()->downloads)->toBe(2);

    $this->actingAs($this->user)->get(route('society.documents.index'))->assertOk()->assertSee('AGM Minutes');

    // Category CRUD
    $this->actingAs($this->user)->post(route('society.documents.categories.store'), ['name' => 'Audit', 'status' => 'active'])->assertRedirect();
    $audit = DocumentCategory::where('name', 'Audit')->firstOrFail();
    $this->actingAs($this->user)->put(route('society.documents.categories.update', $audit), ['name' => 'Audit Reports', 'status' => 'inactive'])->assertRedirect();
    expect($audit->fresh()->name)->toBe('Audit Reports');
    $this->actingAs($this->user)->delete(route('society.documents.categories.destroy', $category))->assertRedirect(); // has documents → refused
    expect(DocumentCategory::whereKey($category->id)->exists())->toBeTrue();
    $this->actingAs($this->user)->delete(route('society.documents.categories.destroy', $audit))->assertRedirect();
    expect(DocumentCategory::whereKey($audit->id)->exists())->toBeFalse();

    $this->actingAs($this->user)->delete(route('society.documents.destroy', $document))->assertRedirect();
    Storage::disk('local')->assertMissing($document->file_path);
});

it('renders society reports with filters and exports Excel and PDF', function () {
    $member = Member::factory()->create(['society_id' => $this->society->id, 'name' => 'Late Payer']);
    MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'member_id' => $member->id, 'member_name' => 'Late Payer', 'flat_number' => 'B-2', 'status' => 'overdue', 'total_amount' => 4000, 'collected_amount' => 0, 'outstanding_amount' => 4000, 'due_date' => now()->subDays(40), 'bill_month' => 'July 2026']);
    CollectionPayment::factory()->create(['society_id' => $this->society->id, 'member_name' => 'Prompt Payer', 'paid_amount' => 1500, 'status' => 'paid', 'payment_mode' => 'upi', 'receipt_date' => now()]);
    $category = ExpenseCategory::factory()->create(['society_id' => $this->society->id, 'name' => 'Utilities']);
    Expense::factory()->create(['society_id' => $this->society->id, 'category_id' => $category->id, 'vendor_id' => null, 'title' => 'Electricity June', 'amount' => 2200, 'expense_date' => now()]);

    $this->actingAs($this->user)->get(route('society.reports.show', 'collection'))->assertOk()->assertSee('Prompt Payer')->assertSee('1,500');
    $this->actingAs($this->user)->get(route('society.reports.show', 'expense'))->assertOk()->assertSee('Electricity June');
    $this->actingAs($this->user)->get(route('society.reports.show', 'defaulter'))->assertOk()->assertSee('Late Payer')->assertSee('4,000');
    $this->actingAs($this->user)->get(route('society.reports.show', ['report' => 'defaulter', 'min_days' => 60]))->assertOk()->assertDontSee('Late Payer');

    $xlsx = $this->actingAs($this->user)->get(route('society.reports.show', ['report' => 'collection', 'export' => 'xlsx']));
    $xlsx->assertOk();
    expect($xlsx->headers->get('content-disposition'))->toContain('.xlsx');

    $pdf = $this->actingAs($this->user)->get(route('society.reports.show', ['report' => 'defaulter', 'export' => 'pdf']));
    $pdf->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('application/pdf');

    $staff = User::factory()->create();
    linkSocietyUser($staff, $this->society, 'staff');
    $this->actingAs($staff)->get(route('society.reports.show', 'collection'))->assertForbidden();
});
