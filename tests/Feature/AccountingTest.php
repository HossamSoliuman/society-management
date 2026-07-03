<?php

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\JournalEntry;
use App\Models\Society;
use App\Models\User;
use App\Services\AccountingService;
use Database\Seeders\AccountGroupSeeder;
use Database\Seeders\AccountingPaymentSeeder;
use Database\Seeders\AccountSeeder;
use Database\Seeders\JournalEntrySeeder;
use Database\Seeders\ReceiptSeeder;
use Database\Seeders\TransactionSeeder;
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

function seedAccountingDemo(): void
{
    test()->seed(AccountGroupSeeder::class);
    test()->seed(AccountSeeder::class);
    test()->seed(TransactionSeeder::class);
    test()->seed(ReceiptSeeder::class);
    test()->seed(AccountingPaymentSeeder::class);
    test()->seed(JournalEntrySeeder::class);
}

it('loads the accounting dashboard with stats and rail', function () {
    seedAccountingDemo();

    $this->actingAs($this->user)->get(route('society.accounting.index'))
        ->assertOk()
        ->assertSee('Accounting')
        ->assertSee('Total Balance')
        ->assertSee('Cash Flow Overview')
        ->assertSee('Recent Transactions')
        ->assertSee('Quick Actions');
});

it('loads the chart of accounts list and groups tabs', function () {
    seedAccountingDemo();

    $this->actingAs($this->user)->get(route('society.accounting.chart-of-accounts'))
        ->assertOk()
        ->assertSee('Chart of Accounts')
        ->assertSee('Account List')
        ->assertSee('Total Accounts');

    $this->actingAs($this->user)->get(route('society.accounting.chart-of-accounts', ['tab' => 'groups']))
        ->assertOk()
        ->assertSee('Account Groups');
});

it('loads the transactions, receipts, payments and journal listings', function () {
    seedAccountingDemo();

    foreach ([
        'society.accounting.transactions',
        'society.accounting.receipts',
        'society.accounting.payments',
        'society.accounting.journal-entries',
    ] as $routeName) {
        $this->actingAs($this->user)->get(route($routeName))->assertOk();
    }
});

it('loads the financial statement pages', function () {
    seedAccountingDemo();

    foreach ([
        'society.accounting.trial-balance',
        'society.accounting.profit-loss',
        'society.accounting.balance-sheet',
        'society.accounting.bank-reconciliation',
        'society.accounting.opening-balances',
    ] as $routeName) {
        $this->actingAs($this->user)->get(route($routeName))->assertOk();
    }
});

it('filters transactions by type', function () {
    seedAccountingDemo();

    $this->actingAs($this->user)
        ->get(route('society.accounting.transactions', ['type' => 'receipt']))
        ->assertOk();
});

it('stores a receipt and redirects with a success message', function () {
    $group = AccountGroup::factory()->create(['society_id' => $this->society->id]);
    $account = Account::factory()->create([
        'society_id' => $this->society->id,
        'group_id' => $group->id,
        'type' => 'detail',
    ]);

    $this->actingAs($this->user)
        ->post(route('society.accounting.receipts.store'), [
            'date' => '2025-05-30',
            'payer_name' => 'Rahul Sharma',
            'flat_no' => 'A-101',
            'receipt_type' => 'maintenance',
            'reference_no' => 'TXN-1001',
            'mode_of_payment' => 'UPI',
            'amount' => 8500,
            'account_id' => $account->id,
            'location' => 'Tower A',
        ])
        ->assertRedirect(route('society.accounting.receipts'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('receipts', [
        'payer_name' => 'Rahul Sharma',
        'amount' => 8500,
    ]);
});

it('stores a payment and redirects with a success message', function () {
    $group = AccountGroup::factory()->create(['society_id' => $this->society->id]);
    $account = Account::factory()->create([
        'society_id' => $this->society->id,
        'group_id' => $group->id,
        'type' => 'detail',
    ]);

    $this->actingAs($this->user)
        ->post(route('society.accounting.payments.store'), [
            'date' => '2025-05-30',
            'payee' => 'City Power Co.',
            'purpose' => 'Electricity bill',
            'mode' => 'Net Banking',
            'amount' => 42000,
            'account_id' => $account->id,
        ])
        ->assertRedirect(route('society.accounting.payments'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('accounting_payments', ['payee' => 'City Power Co.']);
});

it('stores a balanced journal entry with lines', function () {
    $group = AccountGroup::factory()->create(['society_id' => $this->society->id]);
    $debit = Account::factory()->create(['society_id' => $this->society->id, 'group_id' => $group->id, 'type' => 'detail']);
    $credit = Account::factory()->create(['society_id' => $this->society->id, 'group_id' => $group->id, 'type' => 'detail']);

    $this->actingAs($this->user)
        ->post(route('society.accounting.journal-entries.store'), [
            'date' => '2025-05-30',
            'narration' => 'Test entry',
            'lines' => [
                ['account_id' => $debit->id, 'debit' => 5000, 'credit' => 0],
                ['account_id' => $credit->id, 'debit' => 0, 'credit' => 5000],
            ],
        ])
        ->assertRedirect(route('society.accounting.journal-entries'))
        ->assertSessionHas('success');

    $entry = JournalEntry::first();
    expect($entry)->not->toBeNull()
        ->and((float) $entry->total_debit)->toBe(5000.0)
        ->and((float) $entry->total_credit)->toBe(5000.0)
        ->and($entry->lines()->count())->toBe(2);
});

it('stores a new account from the chart of accounts', function () {
    $group = AccountGroup::factory()->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)
        ->post(route('society.accounting.chart-of-accounts.store'), [
            'code' => '9999',
            'name' => 'Test Reserve Fund',
            'group_id' => $group->id,
            'type' => 'detail',
            'opening_balance' => 15000,
            'status' => 'active',
        ])
        ->assertRedirect(route('society.accounting.chart-of-accounts'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('accounts', [
        'code' => '9999',
        'name' => 'Test Reserve Fund',
        'balance' => 15000,
    ]);
});

it('validates journal entries require at least two lines', function () {
    $this->actingAs($this->user)
        ->post(route('society.accounting.journal-entries.store'), [
            'date' => '2025-05-30',
            'lines' => [],
        ])
        ->assertSessionHasErrors('lines');
});

it('computes trial balance debit and credit columns from seeded accounts', function () {
    seedAccountingDemo();

    $tb = app(AccountingService::class)->trialBalance();

    expect($tb['rows'])->not->toBeEmpty()
        ->and($tb['total_debit'])->toBeGreaterThan(0)
        ->and($tb['total_credit'])->toBeGreaterThan(0);

    // Each row lands in exactly one column (debit XOR credit).
    foreach ($tb['rows'] as $row) {
        expect($row['debit'] > 0 xor $row['credit'] > 0)->toBeTrue();
    }
});
