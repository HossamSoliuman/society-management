<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-society key/value preferences edited on Settings → General.
 */
class SocietySetting extends Model
{
    use BelongsToSociety;

    /**
     * Every supported key with its type, default and form metadata.
     *
     * @var array<string, array{type: string, default: mixed, label: string, group: string, min?: int, max?: int}>
     */
    public const DEFINITIONS = [
        'financial_year_start_month' => ['type' => 'month', 'default' => 4, 'label' => 'Financial year starts in', 'group' => 'Accounts'],
        'portal_complaints_enabled' => ['type' => 'bool', 'default' => true, 'label' => 'Residents can raise complaints from the portal', 'group' => 'Resident Portal'],
        'portal_amenity_booking_enabled' => ['type' => 'bool', 'default' => true, 'label' => 'Residents can book amenities from the portal', 'group' => 'Resident Portal'],
        'portal_visitor_preapproval_enabled' => ['type' => 'bool', 'default' => true, 'label' => 'Residents can pre-approve visitors from the portal', 'group' => 'Resident Portal'],
        'amenity_booking_window_days' => ['type' => 'int', 'default' => 30, 'label' => 'Amenities can be booked up to (days ahead)', 'group' => 'Amenities', 'min' => 1, 'max' => 365],
        'visitor_pass_validity_hours' => ['type' => 'int', 'default' => 24, 'label' => 'Visitor pre-approval valid for (hours)', 'group' => 'Gate', 'min' => 1, 'max' => 720],
    ];

    protected $fillable = ['society_id', 'key', 'value'];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Cast a stored string back to the definition's type (default when unset).
     */
    public static function castValue(string $key, ?string $value): mixed
    {
        $definition = self::DEFINITIONS[$key] ?? null;
        if ($definition === null) {
            return $value;
        }
        if ($value === null) {
            return $definition['default'];
        }

        return match ($definition['type']) {
            'bool' => $value === '1',
            'int', 'month' => (int) $value,
            default => $value,
        };
    }
}
