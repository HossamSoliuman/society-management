<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Societies that already have units registered get `total_units` set to
     * that count; societies without unit records keep their declared figure.
     */
    public function up(): void
    {
        $counts = DB::table('units')
            ->whereNull('deleted_at')
            ->selectRaw('society_id, COUNT(*) as total')
            ->groupBy('society_id')
            ->pluck('total', 'society_id');

        foreach ($counts as $societyId => $total) {
            DB::table('societies')->where('id', $societyId)->update(['total_units' => $total]);
        }
    }

    public function down(): void
    {
        //
    }
};
