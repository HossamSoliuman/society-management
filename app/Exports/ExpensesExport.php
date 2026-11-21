<?php

namespace App\Exports;

use App\Models\Expense;

class ExpensesExport extends QueryListExport
{
    public function headings(): array
    {
        return ['Code', 'Date', 'Title', 'Category', 'Vendor', 'Reference', 'Mode', 'Amount', 'Tax', 'Paid', 'Due', 'Status', 'Expense For'];
    }

    /**
     * @param  Expense  $row
     */
    public function map($row): array
    {
        return [
            $row->code,
            $row->expense_date?->format('d M Y'),
            $row->title,
            $row->category?->name,
            $row->vendor?->name,
            $row->reference_no,
            $row->payment_mode,
            (float) $row->amount,
            (float) $row->tax_amount,
            (float) $row->paid_amount,
            (float) $row->due_amount,
            $row->statusLabel(),
            $row->expense_for,
        ];
    }

    public function title(): string
    {
        return 'Expenses';
    }
}
