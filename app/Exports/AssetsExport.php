<?php

namespace App\Exports;

use App\Models\Asset;

class AssetsExport extends QueryListExport
{
    public function headings(): array
    {
        return ['Code', 'Name', 'Category', 'Brand', 'Model', 'Serial No', 'Location', 'Tower / Wing', 'Floor', 'Purchase Date', 'Purchase Cost', 'Current Value', 'Warranty Ends', 'Status', 'Condition'];
    }

    /**
     * @param  Asset  $row
     */
    public function map($row): array
    {
        return [
            $row->asset_code,
            $row->name,
            $row->category?->name,
            $row->brand,
            $row->model,
            $row->serial_number,
            $row->location,
            $row->tower_wing,
            $row->floor,
            $row->purchase_date?->format('d M Y'),
            (float) $row->purchase_cost,
            (float) $row->current_value,
            $row->warranty_end?->format('d M Y'),
            $row->statusLabel(),
            $row->condition ? ucfirst($row->condition) : null,
        ];
    }

    public function title(): string
    {
        return 'Assets';
    }
}
