<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Driver-agnostic SQL fragments for extracting date parts, so the same
 * aggregate query runs on SQLite (tests) and MySQL/MariaDB (production).
 */
final class DateSql
{
    /**
     * Expression yielding the 4-digit year of a date column as an integer.
     */
    public static function year(string $column, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'sqlite' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(YEAR FROM {$column})::int",
            default => "YEAR({$column})",
        };
    }

    /**
     * Expression yielding the month (1-12) of a date column as an integer.
     */
    public static function month(string $column, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(MONTH FROM {$column})::int",
            default => "MONTH({$column})",
        };
    }

    /**
     * Expression yielding the day of month (1-31) of a date column as an integer.
     */
    public static function day(string $column, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'sqlite' => "CAST(strftime('%d', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(DAY FROM {$column})::int",
            default => "DAY({$column})",
        };
    }

    /**
     * Expression yielding a sortable "YYYY-MM" bucket string.
     */
    public static function yearMonth(string $column, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    private static function driver(?string $connection): string
    {
        return DB::connection($connection)->getDriverName();
    }
}
