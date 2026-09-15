<?php

namespace App\Observers;

use App\Models\AccountingPayment;
use App\Models\CollectionPayment;
use App\Models\Expense;
use App\Models\Receipt;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Keeps the ledger in step with source documents: every create/update posts
 * (re-posts) balanced transaction rows, every delete removes them.
 */
class LedgerPostingObserver
{
    public function __construct(private readonly AccountingService $accounting) {}

    public function saved(Model $model): void
    {
        if (! $model->society_id || ! ($society = $model->society)) {
            return;
        }

        match (true) {
            $model instanceof Receipt => $this->postReceipt($model),
            $model instanceof AccountingPayment => $this->postPayment($model),
            $model instanceof CollectionPayment => $this->postCollection($model),
            $model instanceof Expense => $this->postExpense($model),
            default => null,
        };
    }

    public function deleted(Model $model): void
    {
        $this->accounting->unpost($model);
    }

    private function postReceipt(Receipt $receipt): void
    {
        if ($receipt->status !== 'completed' || (float) $receipt->amount <= 0 || ! $receipt->account_id) {
            $this->accounting->unpost($receipt);

            return;
        }

        $society = $receipt->society;
        $incomeAccount = $receipt->income_account_id
            ?: $this->accounting->defaultAccount($society, AccountingService::RECEIPT_TYPE_ACCOUNTS[$receipt->receipt_type] ?? 'other_income')->id;

        $this->accounting->post($society, Carbon::parse($receipt->date), 'receipt', [
            ['account_id' => $receipt->account_id, 'debit' => (float) $receipt->amount],
            ['account_id' => $incomeAccount, 'credit' => (float) $receipt->amount],
        ], $receipt, [
            'reference_no' => $receipt->receipt_no,
            'description' => $receipt->typeLabel().' from '.$receipt->payer_name.($receipt->flat_no ? " ({$receipt->flat_no})" : ''),
            'payment_mode' => $receipt->mode_of_payment,
            'location' => $receipt->location,
        ]);
    }

    private function postPayment(AccountingPayment $payment): void
    {
        if ($payment->status !== 'completed' || (float) $payment->amount <= 0 || ! $payment->account_id) {
            $this->accounting->unpost($payment);

            return;
        }

        $society = $payment->society;
        $expenseAccount = $payment->expense_account_id ?: $this->accounting->defaultAccount($society, 'general_expense')->id;

        $this->accounting->post($society, Carbon::parse($payment->date), 'payment', [
            ['account_id' => $expenseAccount, 'debit' => (float) $payment->amount],
            ['account_id' => $payment->account_id, 'credit' => (float) $payment->amount],
        ], $payment, [
            'reference_no' => $payment->payment_no,
            'description' => trim(($payment->purpose ?: 'Payment').' to '.$payment->payee),
            'payment_mode' => $payment->mode,
        ]);
    }

    private function postCollection(CollectionPayment $payment): void
    {
        if ((float) $payment->paid_amount <= 0 || in_array($payment->status, ['refunded', 'pending'], true)) {
            $this->accounting->unpost($payment);

            return;
        }

        $society = $payment->society;
        $settlement = $this->accounting->settlementAccount($society, $payment->payment_mode);
        $income = $this->accounting->defaultAccount($society, 'maintenance_income');

        $this->accounting->post($society, Carbon::parse($payment->receipt_date), 'receipt', [
            ['account_id' => $settlement->id, 'debit' => (float) $payment->paid_amount],
            ['account_id' => $income->id, 'credit' => (float) $payment->paid_amount],
        ], $payment, [
            'reference_no' => $payment->receipt_number,
            'description' => ($payment->bill_type ?: 'Maintenance').' collection from '.($payment->member_name ?: 'member').($payment->flat_number ? " ({$payment->flat_number})" : ''),
            'payment_mode' => $payment->paymentModeLabel(),
        ]);
    }

    private function postExpense(Expense $expense): void
    {
        $gross = round((float) $expense->amount + (float) $expense->tax_amount, 2);
        if ($gross <= 0 || $expense->payment_status === 'cancelled') {
            $this->accounting->unpost($expense);

            return;
        }

        $society = $expense->society;
        $expenseAccount = $expense->category?->account_id ?: $this->accounting->defaultAccount($society, 'general_expense')->id;
        $paid = min($gross, round((float) $expense->paid_amount, 2));
        $due = round($gross - $paid, 2);

        $lines = [['account_id' => $expenseAccount, 'debit' => $gross]];
        if ($paid > 0) {
            $lines[] = ['account_id' => $this->accounting->settlementAccount($society, $expense->payment_mode)->id, 'credit' => $paid];
        }
        if ($due > 0) {
            $lines[] = ['account_id' => $this->accounting->defaultAccount($society, 'payables')->id, 'credit' => $due];
        }

        $this->accounting->post($society, Carbon::parse($expense->expense_date), 'payment', $lines, $expense, [
            'reference_no' => $expense->code ?: $expense->reference_no,
            'description' => $expense->title.($expense->vendor?->name ? ' — '.$expense->vendor->name : ''),
            'payment_mode' => $expense->payment_mode,
        ]);
    }
}
