<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use App\Models\Concerns\LogsActivity;
use Database\Factories\ParkingAllotmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingAllotment extends Model
{
    use BelongsToSociety, LogsActivity;

    /** @use HasFactory<ParkingAllotmentFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'parking_slot_id', 'unit_id', 'member_id', 'vehicle_id', 'start_date', 'end_date',
        'monthly_charge', 'status', 'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_charge' => 'decimal:2',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class, 'parking_slot_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    protected function activityModule(): string
    {
        return 'Parking';
    }

    protected function activityIdentifier(): ?string
    {
        return $this->slot?->slot_number;
    }
}
