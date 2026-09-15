<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    public const TYPES = ['car' => 'Car', 'bike' => 'Two Wheeler', 'scooter' => 'Scooter', 'cycle' => 'Bicycle', 'other' => 'Other'];

    protected $fillable = [
        'society_id', 'member_id', 'unit_id', 'registration_no', 'vehicle_type', 'make', 'model',
        'color', 'owner_name', 'rfid_tag', 'status', 'notes',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->vehicle_type] ?? ucfirst($this->vehicle_type);
    }
}
