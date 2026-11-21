<?php

namespace App\Exports;

use App\Models\CollectionPayment;

class CollectionPaymentsExport extends QueryListExport
{
    public function headings(): array
    {
        return ['Receipt No', 'Date', 'Member', 'Mobile', 'Flat', 'Bill Period', 'Mode', 'Reference', 'Total Due', 'Paid', 'Discount', 'Fine', 'Balance', 'Status', 'Online', 'Collected By'];
    }

    /**
     * @param  CollectionPayment  $row
     */
    public function map($row): array
    {
        return [
            $row->receipt_number,
            $row->receipt_date?->format('d M Y'),
            $row->member_name,
            $row->member_mobile,
            $row->flat_number,
            $row->bill_period,
            $row->paymentModeLabel(),
            $row->transaction_utr ?: $row->reference_no,
            (float) $row->total_due,
            (float) $row->paid_amount,
            (float) $row->discount,
            (float) $row->fine_penalty,
            (float) $row->balance_due,
            $row->statusLabel(),
            $row->is_online ? 'Yes' : 'No',
            $row->collected_by,
        ];
    }

    public function title(): string
    {
        return 'Collections';
    }
}
