<?php

namespace App\Exports;

use App\Models\Member;

class MembersExport extends QueryListExport
{
    public function headings(): array
    {
        return ['Name', 'Type', 'Flat / Unit', 'Tower / Wing', 'Mobile', 'Email', 'Status', 'Join Date', 'Portal Access'];
    }

    /**
     * @param  Member  $row
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->typeLabel(),
            $row->flat_unit,
            $row->tower_wing,
            $row->mobile,
            $row->email,
            ucfirst((string) $row->status),
            $row->join_date?->format('d M Y'),
            $row->user_id ? 'Yes' : 'No',
        ];
    }

    public function title(): string
    {
        return 'Members';
    }
}
