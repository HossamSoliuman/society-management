<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Society;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\ExpenseCategorySeeder;
use Database\Seeders\ExpenseSeeder;
use Database\Seeders\VendorSeeder;
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

function seedExpenseDemo(): void
{
    test()->seed(ExpenseCategorySeeder::class);
    test()->seed(VendorSeeder::class);
    test()->seed(ExpenseSeeder::class);
}

/**
 * @return array<string, mixed>
 */
function expensePayload(array $overrides = []): array
{
    $category = ExpenseCategory::factory()->create(['society_id' => test()->society->id]);
    $vendor = Vendor::factory()->create(['society_id' => test()->society->id]);

    return array_merge([
        'expense_date' => '2024-05-31',
        'title' => 'Electricity Bill – May 2024',
        'category_id' => $category->id,
        'vendor_id' => $vendor->id,
        'reference_no' => 'EXP-TEST-001',
        'payment_mode' => 'Net Banking',
        'amount' => 18750,
        'tax_amount' => 1250,
        'paid_amount' => 10000,
        'payment_status' => 'pending',
    ], $overrides);
}

it('loads the all-expenses index with stat cards and rail', function () {
    $this->actingAs($this->user)->get(route('society.expenses.index'))
        ->assertOk()
        ->assertSee('Expenses')
        ->assertSee('Total Expenses (This Month)')
        ->assertSee('Expense Overview')
        ->assertSee('Top Categories')
        ->assertSee('Quick Actions');
});

it('shows the exact seeded demo rows on the expenses index', function () {
    seedExpenseDemo();

    $this->actingAs($this->user)->get(route('society.expenses.index'))
        ->assertOk()
        ->assertSee('EXP-2024-056')
        ->assertSee('Electricity Bill – May 2024')
        ->assertSee('Security Salary – May')
        ->assertSee('Showing 1 to 8 of 64 entries');
});

it('loads the add-new-expense form with the live summary', function () {
    $this->actingAs($this->user)->get(route('society.expenses.create'))
        ->assertOk()
        ->assertSee('Add New Expense')
        ->assertSee('Expense Details')
        ->assertSee('Expense Summary')
        ->assertSee('Payment Information');
});

it('stores an expense, computing due and generating a code', function () {
    $this->actingAs($this->user)->post(route('society.expenses.store'), expensePayload())
        ->assertRedirect(route('society.expenses.index'));

    $expense = Expense::latest('id')->first();

    expect($expense)->not->toBeNull()
        ->and((float) $expense->amount)->toBe(18750.0)
        ->and((float) $expense->due_amount)->toBe(10000.0) // 18750 + 1250 - 10000
        ->and($expense->code)->toStartWith('EXP-2024-')
        ->and($expense->society_id)->toBe($this->society->id);
});

it('redirects back to the create form when saving and adding another', function () {
    $this->actingAs($this->user)->post(route('society.expenses.store'), expensePayload(['save_and_add' => '1']))
        ->assertRedirect(route('society.expenses.create'));
});

it('validates required expense fields', function () {
    $this->actingAs($this->user)->post(route('society.expenses.store'), [])
        ->assertSessionHasErrors(['expense_date', 'title', 'category_id', 'vendor_id', 'payment_mode', 'amount', 'paid_amount', 'payment_status']);
});

it('filters the index by the pending tab', function () {
    seedExpenseDemo();

    $this->actingAs($this->user)->get(route('society.expenses.index', ['tab' => 'pending']))
        ->assertOk()
        ->assertSee('Lift Maintenance – May')
        ->assertDontSee('Electricity Bill – May 2024');
});

it('loads the expense categories index with counts', function () {
    seedExpenseDemo();

    $this->actingAs($this->user)->get(route('society.expenses.categories.index'))
        ->assertOk()
        ->assertSee('Expense Categories')
        ->assertSee('Maintenance')
        ->assertSee('Category Usage')
        ->assertSee('Showing 1 to 10 of 12 entries');
});

it('loads the add-new-category form with a preview', function () {
    $this->actingAs($this->user)->get(route('society.expenses.categories.create'))
        ->assertOk()
        ->assertSee('Add New Category')
        ->assertSee('Category Information')
        ->assertSee('Category Settings')
        ->assertSee('Preview')
        ->assertSee('Guidelines');
});

it('stores a category and generates a slug', function () {
    $this->actingAs($this->user)->post(route('society.expenses.categories.store'), [
        'name' => 'Water Charges',
        'icon' => 'fa-bolt',
        'status' => 'active',
        'applicable_for' => 'all_buildings',
        'display_order' => 3,
    ])->assertRedirect(route('society.expenses.categories.index'));

    $category = ExpenseCategory::where('name', 'Water Charges')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('water-charges')
        ->and($category->color)->toBe('blue');
});

it('loads the vendors index and create form', function () {
    seedExpenseDemo();

    $this->actingAs($this->user)->get(route('society.expenses.vendors.index'))
        ->assertOk()
        ->assertSee('Vendors')
        ->assertSee('MSEDCL')
        ->assertSee('Top Vendors');

    $this->actingAs($this->user)->get(route('society.expenses.vendors.create'))
        ->assertOk()
        ->assertSee('Vendor Information');
});

it('stores a vendor', function () {
    $this->actingAs($this->user)->post(route('society.expenses.vendors.store'), [
        'name' => 'Acme Supplies',
        'company' => 'Acme Pvt. Ltd.',
        'email' => 'sales@acme.test',
        'status' => 'active',
    ])->assertRedirect(route('society.expenses.vendors.index'));

    expect(Vendor::where('name', 'Acme Supplies')->exists())->toBeTrue();
});

it('loads the edit forms for expense, category and vendor', function () {
    $category = ExpenseCategory::factory()->create(['society_id' => $this->society->id]);
    $vendor = Vendor::factory()->create(['society_id' => $this->society->id]);
    $expense = Expense::factory()->create([
        'society_id' => $this->society->id,
        'category_id' => $category->id,
        'vendor_id' => $vendor->id,
    ]);

    $this->actingAs($this->user)->get(route('society.expenses.edit', $expense))
        ->assertOk()->assertSee('Edit Expense')->assertSee('Update Expense');
    $this->actingAs($this->user)->get(route('society.expenses.categories.edit', $category))
        ->assertOk()->assertSee('Edit Category')->assertSee('Update Category');
    $this->actingAs($this->user)->get(route('society.expenses.vendors.edit', $vendor))
        ->assertOk()->assertSee('Edit Vendor')->assertSee('Update Vendor');
});

it('updates an expense and recomputes the due amount', function () {
    $category = ExpenseCategory::factory()->create(['society_id' => $this->society->id]);
    $vendor = Vendor::factory()->create(['society_id' => $this->society->id]);
    $expense = Expense::factory()->create([
        'society_id' => $this->society->id,
        'category_id' => $category->id,
        'vendor_id' => $vendor->id,
    ]);

    $this->actingAs($this->user)->put(route('society.expenses.update', $expense), expensePayload([
        'amount' => 5000,
        'tax_amount' => 0,
        'paid_amount' => 2000,
        'category_id' => $category->id,
        'vendor_id' => $vendor->id,
    ]))->assertRedirect(route('society.expenses.index'));

    expect((float) $expense->fresh()->due_amount)->toBe(3000.0);
});

it('loads the expense reports page with a breakdown', function () {
    seedExpenseDemo();

    $this->actingAs($this->user)->get(route('society.expenses.reports'))
        ->assertOk()
        ->assertSee('Expense Reports')
        ->assertSee('Total Expenses')
        ->assertSee('Category-wise Breakdown');
});
