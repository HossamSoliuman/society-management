<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PrefixSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'module', 'prefix', 'starting_number', 'current_number', 'padding', 'status',
    ];

    /**
     * Default prefixes used when a module has no configured row yet.
     *
     * @var array<string, string>
     */
    private const DEFAULT_PREFIXES = [
        'Society' => 'SOC',
        'Subscription' => 'SUB',
        'Invoice' => 'INV',
        'Receipt' => 'RCPT',
        'Refund' => 'RFND',
        'Ticket' => 'TKT',
        'Member' => 'MBR',
    ];

    /**
     * Reserve and return the next number for a platform module, e.g. "INV-2026-0042".
     * The row is locked inside a transaction so concurrent requests never share a number.
     */
    public static function generate(string $module, bool $withYear = true): string
    {
        return DB::transaction(function () use ($module, $withYear): string {
            $setting = static::query()
                ->where('module', $module)
                ->lockForUpdate()
                ->first();

            if (! $setting) {
                $setting = static::query()->create([
                    'module' => $module,
                    'prefix' => self::DEFAULT_PREFIXES[$module] ?? strtoupper(substr($module, 0, 3)),
                    'starting_number' => 1,
                    'current_number' => 0,
                    'padding' => 4,
                    'status' => 'active',
                ]);
            }

            $next = max((int) $setting->current_number + 1, (int) $setting->starting_number);
            $setting->forceFill(['current_number' => $next])->save();

            $sequence = str_pad((string) $next, max(1, (int) $setting->padding), '0', STR_PAD_LEFT);

            return $withYear
                ? "{$setting->prefix}-".now()->format('Y')."-{$sequence}"
                : "{$setting->prefix}-{$sequence}";
        });
    }
}
