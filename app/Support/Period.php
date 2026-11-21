<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * A reporting window picked from a period selector, plus the chart buckets
 * (days or months) that cover it.
 */
final class Period
{
    /** @var array<string, string> */
    public const OPTIONS = [
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'last_3_months' => 'Last 3 Months',
        'last_6_months' => 'Last 6 Months',
        'last_12_months' => 'Last 12 Months',
        'this_year' => 'This Year',
        'last_year' => 'Last Year',
    ];

    private function __construct(
        public readonly string $key,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
    ) {}

    /**
     * Resolve a selector value, falling back to the default for unknown keys.
     */
    public static function fromKey(?string $key, string $default = 'this_month'): self
    {
        $key = array_key_exists((string) $key, self::OPTIONS) ? (string) $key : $default;
        $now = CarbonImmutable::now();

        [$start, $end] = match ($key) {
            'last_month' => [$now->subMonthNoOverflow()->startOfMonth(), $now->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [$now->startOfMonth()->subMonthsNoOverflow(2), $now->endOfMonth()],
            'last_6_months' => [$now->startOfMonth()->subMonthsNoOverflow(5), $now->endOfMonth()],
            'last_12_months' => [$now->startOfMonth()->subMonthsNoOverflow(11), $now->endOfMonth()],
            'this_year' => [$now->startOfYear(), $now->endOfYear()],
            'last_year' => [$now->subYear()->startOfYear(), $now->subYear()->endOfYear()],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };

        return new self($key, $start, $end);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::OPTIONS;
    }

    public function label(): string
    {
        return self::OPTIONS[$this->key];
    }

    /**
     * Number of calendar months the window spans.
     */
    public function months(): int
    {
        return ($this->end->year - $this->start->year) * 12 + $this->end->month - $this->start->month + 1;
    }

    /**
     * The equally long window right before this one, for "vs previous" trends.
     */
    public function previous(): self
    {
        return new self(
            $this->key,
            $this->start->subMonthsNoOverflow($this->months()),
            $this->start->subDay()->endOfDay(),
        );
    }

    /**
     * Label for the comparison window, e.g. "vs last month".
     */
    public function comparisonLabel(): string
    {
        return match ($this->key) {
            'this_month', 'last_month' => 'vs previous month',
            'this_year', 'last_year' => 'vs previous year',
            default => 'vs previous '.$this->months().' months',
        };
    }

    /**
     * Single-month windows chart per day, longer ones per month.
     */
    public function isDaily(): bool
    {
        return $this->months() === 1;
    }

    /**
     * Chart buckets keyed the same way as bucketExpression() so grouped query
     * results can be matched to them.
     *
     * @return array<string, string> bucket key => axis label
     */
    public function buckets(): array
    {
        $buckets = [];

        if ($this->isDaily()) {
            for ($day = $this->start; $day->lte($this->end); $day = $day->addDay()) {
                $buckets[$day->format('Y-m-d')] = $day->format('d M');
            }

            return $buckets;
        }

        for ($month = $this->start->startOfMonth(); $month->lte($this->end); $month = $month->addMonthNoOverflow()) {
            $buckets[$month->format('Y-m')] = $month->format($this->months() > 12 ? 'M y' : 'M');
        }

        return $buckets;
    }

    /**
     * SQL expression grouping a date column into this period's buckets.
     */
    public function bucketExpression(string $column): string
    {
        return $this->isDaily() ? DateSql::date($column) : DateSql::yearMonth($column);
    }

    /**
     * Limit a query to this window on the given date column.
     */
    public function within(Builder $query, string $column): Builder
    {
        return $query
            ->whereDate($column, '>=', $this->start->toDateString())
            ->whereDate($column, '<=', $this->end->toDateString());
    }

    /**
     * Sum a column per bucket for a query already filtered to this window.
     *
     * @return array<int, float> one value per bucket, in bucket order
     */
    public function series(Builder $query, string $dateColumn, string $sumColumn): array
    {
        $expression = $this->bucketExpression($dateColumn);
        $totals = $query
            ->selectRaw("{$expression} as bucket, COALESCE(SUM({$sumColumn}), 0) as total")
            ->groupByRaw($expression)
            ->pluck('total', 'bucket');

        return array_map(
            fn (string $key) => (float) ($totals[$key] ?? 0),
            array_keys($this->buckets()),
        );
    }
}
