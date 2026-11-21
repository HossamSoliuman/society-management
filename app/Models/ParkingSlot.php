<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\ParkingSlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParkingSlot extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<ParkingSlotFactory> */
    use HasFactory;

    public const TYPES = ['car' => 'Four Wheeler', 'bike' => 'Two Wheeler', 'ev' => 'EV Charging', 'visitor' => 'Visitor'];

    protected $fillable = ['society_id', 'slot_number', 'area', 'slot_type', 'is_covered', 'status', 'notes'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_covered' => 'boolean',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function allotments(): HasMany
    {
        return $this->hasMany(ParkingAllotment::class);
    }

    public function activeAllotment(): HasOne
    {
        return $this->hasOne(ParkingAllotment::class)->where('status', 'active');
    }

    /**
     * Open, unblocked slots with no running allotment.
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('status', 'available')->whereDoesntHave('allotments', fn ($q) => $q->where('status', 'active'));
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->slot_type] ?? ucfirst($this->slot_type);
    }

    /**
     * available | allotted | blocked, combining the flag with allotments.
     */
    public function state(): string
    {
        if ($this->status === 'blocked') {
            return 'blocked';
        }

        $active = $this->relationLoaded('activeAllotment') ? $this->activeAllotment : $this->activeAllotment()->first();

        return $active ? 'allotted' : 'available';
    }
}
