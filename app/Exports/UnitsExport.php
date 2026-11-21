<?php

namespace App\Exports;

use App\Models\Unit;

class UnitsExport extends QueryListExport
{
    public function headings(): array
    {
        return ['Unit', 'Building', 'Wing', 'Floor', 'Type', 'Area (sq ft)', 'Status', 'Owner', 'Owner Mobile', 'Occupied By', 'Occupant Role'];
    }

    /**
     * @param  Unit  $row
     */
    public function map($row): array
    {
        return [
            $row->unit_number,
            $row->building,
            $row->wing,
            $row->floor,
            $row->unit_type,
            $row->area_sqft,
            $row->statusLabel(),
            $row->owner_name,
            $row->owner_mobile,
            $row->occupied_by_name,
            $row->occupied_by_role ? ucfirst($row->occupied_by_role) : null,
        ];
    }

    public function title(): string
    {
        return 'Units';
    }
}
