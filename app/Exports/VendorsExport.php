<?php

namespace App\Exports;

use App\Models\Vendor;

class VendorsExport extends QueryListExport
{
    public function headings(): array
    {
        return ['Name', 'Company', 'Category', 'Phone', 'Email', 'GST No', 'Address', 'Status'];
    }

    /**
     * @param  Vendor  $row
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->company,
            $row->category?->name,
            $row->phone,
            $row->email,
            $row->gst_number,
            $row->address,
            $row->statusLabel(),
        ];
    }

    public function title(): string
    {
        return 'Vendors';
    }
}
