<?php

namespace App\Exports;

use App\Models\MaintenanceBill;

class MaintenanceBillsExport extends QueryListExport
{
    public function headings(): array
    {
        return ['Bill No', 'Bill Date', 'Due Date', 'Month', 'Cycle', 'Member', 'Flat', 'Tower / Wing', 'Sub Total', 'Discount', 'Late Fee', 'Tax', 'Previous Dues', 'Total', 'Collected', 'Outstanding', 'Status'];
    }

    /**
     * @param  MaintenanceBill  $row
     */
    public function map($row): array
    {
        return [
            $row->bill_number,
            $row->bill_date?->format('d M Y'),
            $row->due_date?->format('d M Y'),
            $row->bill_month,
            $row->bill_cycle,
            $row->member_name,
            $row->flat_number,
            $row->tower_wing,
            (float) $row->sub_total,
            (float) $row->discount,
            (float) $row->late_fee,
            (float) $row->tax_amount,
            (float) $row->previous_dues,
            (float) $row->total_amount,
            (float) $row->collected_amount,
            (float) $row->outstanding_amount,
            $row->statusLabel(),
        ];
    }

    public function title(): string
    {
        return 'Maintenance Bills';
    }
}
