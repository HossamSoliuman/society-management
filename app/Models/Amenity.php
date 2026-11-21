<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\AmenityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Amenity extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<AmenityFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'name', 'description', 'location', 'capacity', 'open_time', 'close_time',
        'max_hours', 'charge_per_hour', 'requires_approval', 'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'max_hours' => 'integer',
            'charge_per_hour' => 'decimal:2',
            'requires_approval' => 'boolean',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(AmenityBooking::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hoursLabel(): string
    {
        return substr((string) $this->open_time, 0, 5).' – '.substr((string) $this->close_time, 0, 5);
    }
}
