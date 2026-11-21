<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Society;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Data behind the super-admin Society / Revenue / Subscription / Payment
 * reports. Every report returns the same shape so the screen, Excel and PDF
 * share it: ['title', 'period', 'headings', 'rows', 'summary' => [label => value]].
 * A null bound in the period means that side of the date range is open.
 */
class PlatformReportService
{
    public const REPORTS = [
        'society' => 'Society Report',
        'revenue' => 'Revenue Report',
        'subscription' => 'Subscription Report',
        'payment' => 'Payment Report',
    ];

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function build(string $report, ?Carbon $from, ?Carbon $to, array $filters = []): array
    {
        return match ($report) {
            'society' => $this->society($from, $to, $filters),
            'revenue' => $this->revenue($from, $to, $filters),
            'subscription' => $this->subscription($from, $to, $filters),
            default => $this->payment($from, $to, $filters),
        };
    }

    /**
     * Societies that joined the platform within the period.
     *
     * @param  array<string, mixed>  $filters  status, type, q
     * @return array<string, mixed>
     */
    public function society(?Carbon $from, ?Carbon $to, array $filters = []): array
    {
        $societies = Society::query()
            ->with(['societyType', 'subscriptionPlan'])
            ->tap(fn (Builder $q) => $this->within($q, 'created_at', $from, $to))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('society_type_id', $type))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('registration_number', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->get();

        return [
            'title' => self::REPORTS['society'],
            'period' => $this->period($from, $to),
            'headings' => ['Society', 'Registration No', 'Type', 'City', 'Units', 'Plan', 'Subscription Ends', 'Subscription', 'Status', 'Joined'],
            'rows' => $societies->map(fn (Society $s) => [
                $s->name,
                $s->registration_number,
                $s->societyType?->name,
                $s->city,
                (int) $s->flats_count + (int) $s->shops_count + (int) $s->offices_count,
                $s->subscriptionPlan?->name,
                $s->subscription_end_date ? Carbon::parse($s->subscription_end_date)->format('d M Y') : null,
                $s->subscription_status ? ucfirst((string) $s->subscription_status) : null,
                ucfirst((string) $s->status),
                $s->created_at?->format('d M Y'),
            ])->all(),
            'summary' => [
                'Societies' => $societies->count(),
                'Active' => $societies->where('status', 'active')->count(),
                'Inactive' => $societies->where('status', '!=', 'active')->count(),
                'Units' => number_format($societies->sum(fn (Society $s) => (int) $s->flats_count + (int) $s->shops_count + (int) $s->offices_count)),
            ],
        ];
    }

    /**
     * Successful platform payments collected within the period.
     *
     * @param  array<string, mixed>  $filters  method, society
     * @return array<string, mixed>
     */
    public function revenue(?Carbon $from, ?Carbon $to, array $filters = []): array
    {
        $payments = $this->payments($from, $to, $filters)->where('status', 'success')->get();
        $refunded = (float) $payments->sum(fn (Payment $p) => (float) $p->refunds->where('status', 'completed')->sum('amount'));
        $collected = (float) $payments->sum('amount');

        return [
            'title' => self::REPORTS['revenue'],
            'period' => $this->period($from, $to),
            'headings' => ['Receipt No', 'Date', 'Society', 'Invoice', 'Method', 'Transaction ID', 'Amount', 'Refunded', 'Net'],
            'rows' => $payments->map(function (Payment $p) {
                $refunded = (float) $p->refunds->where('status', 'completed')->sum('amount');

                return [
                    $p->receipt_number,
                    $p->payment_date?->format('d M Y'),
                    $p->society?->name,
                    $p->invoice?->invoice_number,
                    $p->payment_method,
                    $p->transaction_id,
                    (float) $p->amount,
                    $refunded,
                    (float) $p->amount - $refunded,
                ];
            })->all(),
            'summary' => [
                'Collected' => format_inr($collected),
                'Refunded' => format_inr($refunded),
                'Net Revenue' => format_inr($collected - $refunded),
                'Transactions' => $payments->count(),
            ],
        ];
    }

    /**
     * Subscriptions that started within the period.
     *
     * @param  array<string, mixed>  $filters  status, plan, q
     * @return array<string, mixed>
     */
    public function subscription(?Carbon $from, ?Carbon $to, array $filters = []): array
    {
        $subscriptions = Subscription::query()
            ->with(['society', 'plan'])
            ->tap(fn (Builder $q) => $this->within($q, 'start_date', $from, $to, dateOnly: true))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['plan'] ?? null, fn ($q, $plan) => $q->where('plan_id', $plan))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(fn ($sub) => $sub->where('subscription_number', 'like', "%{$term}%")
                    ->orWhereHas('society', fn ($s) => $s->where('name', 'like', "%{$term}%")));
            })
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        return [
            'title' => self::REPORTS['subscription'],
            'period' => $this->period($from, $to),
            'headings' => ['Subscription No', 'Society', 'Plan', 'Start', 'End', 'Cycle', 'Amount', 'Status'],
            'rows' => $subscriptions->map(fn (Subscription $s) => [
                $s->subscription_number,
                $s->society?->name,
                $s->plan?->name,
                $s->start_date ? Carbon::parse($s->start_date)->format('d M Y') : null,
                $s->end_date ? Carbon::parse($s->end_date)->format('d M Y') : null,
                $s->billing_cycle ? ucfirst(str_replace('_', ' ', $s->billing_cycle)) : null,
                (float) $s->amount,
                $s->statusLabel(),
            ])->all(),
            'summary' => [
                'Subscriptions' => $subscriptions->count(),
                'Active' => $subscriptions->where('status', 'active')->count(),
                'Expired' => $subscriptions->where('status', 'expired')->count(),
                'Contract Value' => format_inr($subscriptions->sum('amount')),
            ],
        ];
    }

    /**
     * Every platform payment (any status) within the period.
     *
     * @param  array<string, mixed>  $filters  status, method, society
     * @return array<string, mixed>
     */
    public function payment(?Carbon $from, ?Carbon $to, array $filters = []): array
    {
        $payments = $this->payments($from, $to, $filters)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->get();

        return [
            'title' => self::REPORTS['payment'],
            'period' => $this->period($from, $to),
            'headings' => ['Receipt No', 'Date', 'Society', 'Invoice', 'Method', 'Transaction ID', 'Recorded By', 'Amount', 'Status'],
            'rows' => $payments->map(fn (Payment $p) => [
                $p->receipt_number,
                $p->payment_date?->format('d M Y'),
                $p->society?->name,
                $p->invoice?->invoice_number,
                $p->payment_method,
                $p->transaction_id,
                $p->recordedBy?->name,
                (float) $p->amount,
                ucfirst((string) $p->status),
            ])->all(),
            'summary' => [
                'Payments' => $payments->count(),
                'Successful' => $payments->where('status', 'success')->count(),
                'Pending / Failed' => $payments->where('status', '!=', 'success')->count(),
                'Amount' => format_inr($payments->sum('amount')),
            ],
        ];
    }

    /**
     * Payment query shared by the revenue and payment reports.
     *
     * @param  array<string, mixed>  $filters
     */
    private function payments(?Carbon $from, ?Carbon $to, array $filters): Builder
    {
        return Payment::query()
            ->with(['society', 'invoice', 'recordedBy', 'refunds'])
            ->tap(fn (Builder $q) => $this->within($q, 'payment_date', $from, $to, dateOnly: true))
            ->when($filters['method'] ?? null, fn ($q, $method) => $q->where('payment_method', $method))
            ->when($filters['society'] ?? null, fn ($q, $society) => $q->where('society_id', $society))
            ->orderByDesc('payment_date')
            ->orderByDesc('id');
    }

    private function within(Builder $query, string $column, ?Carbon $from, ?Carbon $to, bool $dateOnly = false): void
    {
        if ($dateOnly) {
            $query->when($from, fn ($q) => $q->whereDate($column, '>=', $from->toDateString()))
                ->when($to, fn ($q) => $q->whereDate($column, '<=', $to->toDateString()));

            return;
        }

        $query->when($from, fn ($q) => $q->where($column, '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($q) => $q->where($column, '<=', $to->copy()->endOfDay()));
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function period(?Carbon $from, ?Carbon $to): array
    {
        return [$from?->toDateString(), $to?->toDateString()];
    }
}
