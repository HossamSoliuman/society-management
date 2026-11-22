<?php

namespace App\Console\Commands;

use App\Models\AccountingPayment;
use App\Models\CollectionPayment;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Receipt;
use App\Observers\LedgerPostingObserver;
use App\Services\AccountingService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PostMissingLedgerEntries extends Command
{
    protected $signature = 'ledger:post-missing';

    protected $description = 'Post receipts, payments, collections, expenses and journals that have no ledger rows yet (e.g. data entered before the ledger existed)';

    public function handle(LedgerPostingObserver $observer, AccountingService $accounting): int
    {
        $total = 0;

        foreach ([Receipt::class, AccountingPayment::class, CollectionPayment::class, Expense::class] as $class) {
            $posted = 0;
            $this->unposted($class)->chunkById(100, function ($rows) use ($observer, &$posted) {
                foreach ($rows as $row) {
                    $observer->saved($row);
                    $posted++;
                }
            });
            $this->line(class_basename($class).": {$posted}");
            $total += $posted;
        }

        $journals = 0;
        $this->unposted(JournalEntry::class)->where('status', 'posted')->chunkById(100, function ($entries) use ($accounting, &$journals) {
            foreach ($entries as $entry) {
                $accounting->postJournalEntry($entry);
                $journals++;
            }
        });
        $this->line("JournalEntry: {$journals}");

        $this->info('Checked '.($total + $journals).' document(s) without ledger rows.');

        return self::SUCCESS;
    }

    /**
     * Society documents that have no transactions pointing back at them.
     *
     * @param  class-string<Model>  $class
     * @return Builder<Model>
     */
    private function unposted(string $class): Builder
    {
        $model = new $class;

        return $class::query()
            ->whereNotNull('society_id')
            ->whereNotExists(fn (QueryBuilder $query) => $query->selectRaw('1')
                ->from('transactions')
                ->where('transactions.source_type', $model->getMorphClass())
                ->whereColumn('transactions.source_id', $model->getTable().'.'.$model->getKeyName()));
    }
}
