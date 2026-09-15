<?php

use App\Models\Account;
use App\Models\AccountingPayment;
use App\Models\BankStatementLine;
use App\Models\CollectionPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JournalEntry;
use App\Models\Receipt;
use App\Models\Society;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->society = Society::create(['name' => 'Ledger Society', 'prefix' => 'LS', 'status' => 'active']);
    linkSocietyAdmin($this->user, $this->society);

    $this->accounting = app(AccountingService::class);
    $this->accounting->seedChartFor($this->society);

    $this->bank = $this->accounting->defaultAccount($this->society, 'bank');
    $this->cash = $this->accounting->defaultAccount($this->society, 'cash');
    $this->maintenanceIncome = $this->accounting->defaultAccount($this->society, 'maintenance_income');
});

function ledgerRows(Society $society): array
{
    return Transaction::query()->forSociety($society)->get()->map(fn ($t) => [$t->account_id, (float) $t->debit, (float) $t->credit])->all();
}

it('seeds a usable chart of accounts per society and on society creation', function () {
    expect(Account::forSociety($this->society)->where('type', 'detail')->count())->toBeGreaterThanOrEqual(count(AccountingService::DEFAULT_ACCOUNTS))
        ->and($this->bank->is_bank)->toBeTrue()
        ->and($this->bank->group->kind)->toBe('asset')
        ->and($this->maintenanceIncome->group->kind)->toBe('income');

    // Idempotent
    $this->accounting->seedChartFor($this->society);
    expect(Account::forSociety($this->society)->where('system_key', 'bank')->count())->toBe(1);
});

it('posts a receipt as Bank Dr / Maintenance Income Cr and reverses it on delete', function () {
    $receipt = Receipt::create([
        'society_id' => $this->society->id, 'receipt_no' => 'R-1', 'date' => now(), 'payer_name' => 'A-101',
        'receipt_type' => 'maintenance', 'mode_of_payment' => 'UPI', 'amount' => 1000, 'account_id' => $this->bank->id, 'status' => 'completed',
    ]);

    $rows = Transaction::where('source_type', $receipt->getMorphClass())->where('source_id', $receipt->id)->get();
    expect($rows)->toHaveCount(2)
        ->and((float) $rows->firstWhere('account_id', $this->bank->id)->debit)->toBe(1000.0)
        ->and((float) $rows->firstWhere('account_id', $this->maintenanceIncome->id)->credit)->toBe(1000.0)
        ->and($this->accounting->balance($this->bank))->toBe(1000.0)
        ->and($this->accounting->balance($this->maintenanceIncome))->toBe(1000.0)
        ->and((float) $this->bank->fresh()->balance)->toBe(1000.0);

    $receipt->delete();
    expect(Transaction::count())->toBe(0)
        ->and((float) $this->bank->fresh()->balance)->toBe(0.0);
});

it('posts accounting payments, collections and expenses to the ledger', function () {
    AccountingPayment::create([
        'society_id' => $this->society->id, 'payment_no' => 'P-1', 'date' => now(), 'payee' => 'Vendor', 'purpose' => 'Repairs',
        'mode' => 'Cheque', 'amount' => 400, 'account_id' => $this->bank->id, 'status' => 'completed',
    ]);
    CollectionPayment::factory()->create([
        'society_id' => $this->society->id, 'paid_amount' => 2500, 'payment_mode' => 'cash', 'status' => 'paid', 'receipt_date' => now(),
    ]);
    $category = ExpenseCategory::factory()->create(['society_id' => $this->society->id, 'account_id' => $this->accounting->defaultAccount($this->society, 'utility_expense')->id]);
    Expense::factory()->create([
        'society_id' => $this->society->id, 'category_id' => $category->id, 'vendor_id' => null, 'amount' => 1000, 'tax_amount' => 180,
        'paid_amount' => 500, 'due_amount' => 680, 'payment_mode' => 'upi', 'payment_status' => 'pending', 'expense_date' => now(),
    ]);

    expect($this->accounting->balance($this->accounting->defaultAccount($this->society, 'general_expense')))->toBe(400.0)
        ->and($this->accounting->balance($this->bank))->toBe(-400.0 - 500.0)
        ->and($this->accounting->balance($this->cash))->toBe(2500.0)
        ->and($this->accounting->balance($this->maintenanceIncome))->toBe(2500.0)
        ->and($this->accounting->balance($this->accounting->defaultAccount($this->society, 'utility_expense')))->toBe(1180.0)
        ->and($this->accounting->balance($this->accounting->defaultAccount($this->society, 'payables')))->toBe(680.0);

    $tb = $this->accounting->trialBalance($this->society);
    expect($tb['total_debit'])->toBe($tb['total_credit']);
});

it('records receipts and payments through the screens with series numbering', function () {
    $this->actingAs($this->user)->post(route('society.accounting.receipts.store'), [
        'date' => now()->toDateString(), 'payer_name' => 'Rahul', 'receipt_type' => 'amenities', 'mode_of_payment' => 'Cash',
        'amount' => 300, 'account_id' => $this->cash->id,
    ])->assertRedirect(route('society.accounting.receipts'));

    $this->actingAs($this->user)->post(route('society.accounting.payments.store'), [
        'date' => now()->toDateString(), 'payee' => 'Electric Co', 'mode' => 'Net Banking', 'amount' => 120, 'account_id' => $this->bank->id,
        'expense_account_id' => $this->accounting->defaultAccount($this->society, 'utility_expense')->id,
    ])->assertRedirect(route('society.accounting.payments'));

    $receipt = Receipt::first();
    $payment = AccountingPayment::first();
    expect($receipt->receipt_no)->toStartWith('RCPT/')
        ->and($payment->payment_no)->toStartWith('PMT/')
        ->and($this->accounting->balance($this->accounting->defaultAccount($this->society, 'amenities_income')))->toBe(300.0)
        ->and($this->accounting->balance($this->accounting->defaultAccount($this->society, 'utility_expense')))->toBe(120.0);

    // second receipt increments the series
    $this->actingAs($this->user)->post(route('society.accounting.receipts.store'), [
        'date' => now()->toDateString(), 'payer_name' => 'Priya', 'receipt_type' => 'maintenance', 'mode_of_payment' => 'UPI',
        'amount' => 50, 'account_id' => $this->bank->id,
    ]);
    expect(Receipt::pluck('receipt_no')->unique())->toHaveCount(2);

    $this->actingAs($this->user)->get(route('society.accounting.index'))->assertOk()->assertSee('Bank Account');
    $this->actingAs($this->user)->get(route('society.accounting.transactions'))->assertOk();
    $this->actingAs($this->user)->get(route('society.accounting.receipts'))->assertOk()->assertSee('Amenities');
    $this->actingAs($this->user)->get(route('society.accounting.payments'))->assertOk();
});

it('rejects unbalanced journal entries and posts balanced ones', function () {
    $expense = $this->accounting->defaultAccount($this->society, 'admin_expense');

    $this->actingAs($this->user)->post(route('society.accounting.journal-entries.store'), [
        'date' => now()->toDateString(), 'narration' => 'Oops',
        'lines' => [['account_id' => $expense->id, 'debit' => 100], ['account_id' => $this->bank->id, 'credit' => 90]],
    ])->assertSessionHasErrors('lines');

    $this->actingAs($this->user)->post(route('society.accounting.journal-entries.store'), [
        'date' => now()->toDateString(), 'narration' => 'Adjustment',
        'lines' => [['account_id' => $expense->id, 'debit' => 100], ['account_id' => $this->bank->id, 'credit' => 100]],
    ])->assertRedirect(route('society.accounting.journal-entries'));

    $entry = JournalEntry::first();
    expect($entry->entry_no)->toStartWith('JV/')
        ->and(Transaction::where('source_type', $entry->getMorphClass())->count())->toBe(2)
        ->and($this->accounting->balance($expense))->toBe(100.0);
});

it('saves opening balances as a balanced journal entry at the financial-year start', function () {
    $fixed = $this->accounting->defaultAccount($this->society, 'fixed_assets');
    $reserve = $this->accounting->defaultAccount($this->society, 'reserve_fund');

    $this->actingAs($this->user)->put(route('society.accounting.opening-balances.update'), [
        'financial_year_start' => '2026-04-01',
        'balances' => [$this->bank->id => 50000, $fixed->id => 200000, $reserve->id => 100000],
    ])->assertRedirect(route('society.accounting.opening-balances'));

    $entry = JournalEntry::where('is_opening', true)->firstOrFail();
    $equity = $this->accounting->defaultAccount($this->society, 'opening_equity');
    expect($entry->date->toDateString())->toBe('2026-04-01')
        ->and($this->accounting->balance($this->bank))->toBe(50000.0)
        ->and($this->accounting->balance($equity))->toBe(150000.0);

    $tb = $this->accounting->trialBalance($this->society);
    expect($tb['total_debit'])->toBe(250000.0)->and($tb['total_credit'])->toBe(250000.0);

    // Re-saving replaces rather than duplicates.
    $this->actingAs($this->user)->put(route('society.accounting.opening-balances.update'), [
        'financial_year_start' => '2026-04-01',
        'balances' => [$this->bank->id => 60000, $fixed->id => 200000, $reserve->id => 100000],
    ]);
    expect(JournalEntry::where('is_opening', true)->count())->toBe(1)
        ->and($this->accounting->balance($this->bank))->toBe(60000.0)
        ->and($this->accounting->balance($equity))->toBe(160000.0);

    $this->actingAs($this->user)->get(route('society.accounting.opening-balances'))->assertOk()->assertSee($entry->entry_no);
});

it('derives trial balance, P&L and balance sheet from the ledger for a period', function () {
    $this->travelTo(Carbon::parse('2026-06-15'));
    Receipt::create(['society_id' => $this->society->id, 'receipt_no' => 'R-1', 'date' => '2026-05-10', 'payer_name' => 'x', 'receipt_type' => 'maintenance', 'mode_of_payment' => 'UPI', 'amount' => 10000, 'account_id' => $this->bank->id, 'status' => 'completed']);
    Receipt::create(['society_id' => $this->society->id, 'receipt_no' => 'R-2', 'date' => '2026-06-10', 'payer_name' => 'y', 'receipt_type' => 'maintenance', 'mode_of_payment' => 'UPI', 'amount' => 8000, 'account_id' => $this->bank->id, 'status' => 'completed']);
    AccountingPayment::create(['society_id' => $this->society->id, 'payment_no' => 'P-1', 'date' => '2026-06-05', 'payee' => 'v', 'mode' => 'Cash', 'amount' => 3000, 'account_id' => $this->bank->id, 'status' => 'completed']);

    $pl = $this->accounting->profitAndLoss($this->society, Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'));
    expect($pl['total_income_value'])->toBe(8000.0)
        ->and($pl['total_expenses_value'])->toBe(3000.0)
        ->and($pl['net'])->toBe(5000.0)
        ->and($pl['income_rows'][0][0])->toBe('Maintenance Income');

    $bs = $this->accounting->balanceSheet($this->society, Carbon::parse('2026-06-30'));
    expect($bs['balanced'])->toBeTrue()
        ->and($bs['total_assets_value'])->toBe(15000.0)
        ->and($bs['surplus'])->toBe(15000.0);

    $tb = $this->accounting->trialBalance($this->society, Carbon::parse('2026-05-31'));
    expect($tb['total_debit'])->toBe(10000.0)->and($tb['total_credit'])->toBe(10000.0);

    $this->actingAs($this->user)->get(route('society.accounting.profit-loss', ['from' => '2026-06-01', 'to' => '2026-06-30']))
        ->assertOk()->assertSee('8,000.00')->assertSee('5,000.00');
    $this->actingAs($this->user)->get(route('society.accounting.balance-sheet', ['as_on' => '2026-06-30']))
        ->assertOk()->assertSee('15,000.00');
    $this->actingAs($this->user)->get(route('society.accounting.trial-balance', ['as_on' => '2026-05-31']))
        ->assertOk()->assertSee('10,000.00');
});

it('imports a bank statement and matches lines against ledger rows', function () {
    Receipt::create(['society_id' => $this->society->id, 'receipt_no' => 'R-1', 'date' => '2026-06-10', 'payer_name' => 'x', 'receipt_type' => 'maintenance', 'mode_of_payment' => 'UPI', 'amount' => 2500, 'account_id' => $this->bank->id, 'reference_no' => 'UTR777', 'status' => 'completed']);
    AccountingPayment::create(['society_id' => $this->society->id, 'payment_no' => 'P-1', 'date' => '2026-06-12', 'payee' => 'v', 'mode' => 'Net Banking', 'amount' => 900, 'account_id' => $this->bank->id, 'status' => 'completed']);

    $csv = implode("\n", [
        'Date,Description,Ref No,Debit,Credit,Balance',
        '11/06/2026,UPI credit A-101,UTR777,,2500,52500',
        '12/06/2026,NEFT vendor,,900,,51600',
        '13/06/2026,Bank charges,,50,,51550',
    ]);

    $this->actingAs($this->user)->post(route('society.accounting.bank-reconciliation.import'), [
        'account_id' => $this->bank->id,
        'file' => UploadedFile::fake()->createWithContent('statement.csv', $csv),
    ])->assertRedirect(route('society.accounting.bank-reconciliation', ['account' => $this->bank->id]));

    expect(BankStatementLine::count())->toBe(3)
        ->and(BankStatementLine::whereNotNull('matched_at')->count())->toBe(2)
        ->and(Transaction::where('account_id', $this->bank->id)->whereNotNull('reconciled_at')->count())->toBe(2);

    $this->actingAs($this->user)->get(route('society.accounting.bank-reconciliation', ['account' => $this->bank->id]))
        ->assertOk()
        ->assertSee('Bank charges')
        ->assertSee('Matched')
        ->assertSee('51,550.00');

    $line = BankStatementLine::whereNotNull('matched_at')->first();
    $this->actingAs($this->user)->post(route('society.accounting.bank-reconciliation.unmatch', $line))->assertRedirect();
    expect($line->fresh()->matched_at)->toBeNull();
});
