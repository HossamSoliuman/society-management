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
     * Platform modules that take their numbers from this table, with default prefixes.
     *
     * @var array<string, string>
     */
    public const MODULES = [
        'Subscription' => 'SUB',
        'Invoice' => 'INV',
        'Receipt' => 'RCPT',
        'Refund' => 'RFND',
        'Ticket' => 'TKT',
    ];

    /**
     * Reserve and return the next number for a platform module, e.g. "INV-2026-0042".
     * The counter is bumped with a single UPDATE before it is read back, so the row
     * stays write-locked until commit and concurrent requests never share a number.
     */
    public static function generate(string $module, bool $withYear = true): string
    {
        return DB::transaction(function () use ($module, $withYear): string {
            $setting = static::forModule($module);

            static::query()->whereKey($setting->getKey())->update([
                'current_number' => DB::raw('CASE WHEN current_number + 1 < starting_number THEN starting_number ELSE current_number + 1 END'),
            ]);

            $setting->refresh();

            return $setting->format((int) $setting->current_number, $withYear);
        });
    }

    /**
     * Row for a module, created with its default prefix on first use.
     */
    public static function forModule(string $module): self
    {
        return static::query()->where('module', $module)->orderBy('id')->first()
            ?? static::query()->create([
                'module' => $module,
                'prefix' => self::MODULES[$module] ?? strtoupper(substr($module, 0, 3)),
                'starting_number' => 1,
                'current_number' => 0,
                'padding' => 4,
                'status' => 'active',
            ]);
    }

    /**
     * Sequence the next generated number will use.
     */
    public function nextNumber(): int
    {
        return max((int) $this->current_number + 1, (int) $this->starting_number);
    }

    public function format(int $sequence, bool $withYear = true): string
    {
        $padded = str_pad((string) $sequence, max(1, (int) $this->padding), '0', STR_PAD_LEFT);

        return $withYear
            ? "{$this->prefix}-".now()->format('Y')."-{$padded}"
            : "{$this->prefix}-{$padded}";
    }
}
