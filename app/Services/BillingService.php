<?php

namespace App\Services;

use App\Jobs\SendMaintenanceBill;
use App\Models\BillSetting;
use App\Models\ChargeHead;
use App\Models\LateFeeSetting;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\NumberingSeries;
use App\Models\Society;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Maintenance billing engine: bulk generation from charge heads, bulk import
 * from spreadsheet rows, overdue / late-fee processing and totals upkeep.
 */
class BillingService
{
    /**
     * Preview what generateForPeriod() would create, without persisting.
     *
     * @param  array<int, int>  $chargeHeadIds
     * @param  array<string, mixed>  $options
     * @return array{units: int, skipped: int, total: float, lines: Collection<int, ChargeHead>}
     */
    public function previewForPeriod(Society $society, string $billMonth, array $chargeHeadIds, array $options = []): array
    {
        $heads = $this->chargeHeads($society, $chargeHeadIds);
        $taxes = $this->activeTaxes($society);
        $units = $this->billableUnits($society, $options['unit_ids'] ?? null);
        $alreadyBilled = $this->unitsBilledFor($society, $billMonth);

        $count = 0;
        $skipped = 0;
        $total = 0.0;

        foreach ($units as $unit) {
            if ($alreadyBilled->contains($unit->id)) {
                $skipped++;

                continue;
            }
            $lines = $this->linesFor($unit, $heads);
            $subTotal = array_sum(array_column($lines, 'amount'));
            $total += $subTotal + $this->taxFor($subTotal, $taxes);
            $count++;
        }

        return ['units' => $count, 'skipped' => $skipped, 'total' => round($total, 2), 'lines' => $heads];
    }

    /**
     * Create one bill per billable unit for a period. Units that already have
     * a bill for the month are skipped so re-running is safe.
     *
     * @param  array<int, int>  $chargeHeadIds
     * @param  array<string, mixed>  $options  bill_date, due_date, bill_cycle, billing_type, unit_ids, send_email, send_sms, notes
     * @return Collection<int, MaintenanceBill>
     */
    public function generateForPeriod(Society $society, string $billMonth, array $chargeHeadIds, array $options = []): Collection
    {
        $heads = $this->chargeHeads($society, $chargeHeadIds);
        if ($heads->isEmpty()) {
            throw new InvalidArgumentException('Select at least one active charge head.');
        }

        $settings = $this->settings($society);
        $taxes = $this->activeTaxes($society);
        $billDate = isset($options['bill_date']) ? Carbon::parse($options['bill_date']) : Carbon::today();
        $dueDate = isset($options['due_date']) ? Carbon::parse($options['due_date']) : $billDate->copy()->addDays((int) ($settings->due_date_days ?: 15));
        $alreadyBilled = $this->unitsBilledFor($society, $billMonth);

        $bills = new Collection;

        DB::transaction(function () use ($society, $billMonth, $heads, $settings, $taxes, $billDate, $dueDate, $alreadyBilled, $options, &$bills) {
            foreach ($this->billableUnits($society, $options['unit_ids'] ?? null) as $unit) {
                if ($alreadyBilled->contains($unit->id)) {
                    continue;
                }

                $lines = $this->linesFor($unit, $heads);
                $subTotal = round(array_sum(array_column($lines, 'amount')), 2);
                if ($subTotal <= 0 && ! $settings->allow_zero_amount_bills) {
                    continue;
                }

                $member = $this->memberFor($unit);
                $bill = $this->createBill($society, [
                    'member' => $member,
                    'unit' => $unit,
                    'bill_month' => $billMonth,
                    'bill_date' => $billDate,
                    'due_date' => $dueDate,
                    'bill_cycle' => $options['bill_cycle'] ?? $billMonth,
                    'billing_type' => $options['billing_type'] ?? ($settings->default_bill_type ?: 'Monthly Maintenance'),
                    'lines' => $lines,
                    'taxes' => $taxes,
                    'include_previous_dues' => (bool) $settings->include_previous_dues,
                    'collection_account' => $settings->default_collection_account,
                    'notes' => $options['notes'] ?? null,
                    'send_email' => (bool) ($options['send_email'] ?? $settings->auto_email_bill),
                    'send_sms' => (bool) ($options['send_sms'] ?? $settings->auto_sms_bill),
                ]);

                $bills->push($bill);
            }
        });

        $this->queueDelivery($bills);

        return $bills;
    }

    /**
     * Create bills from validated spreadsheet rows. Rows sharing flat + month
     * collapse into one bill with one line per row.
     *
     * @param  array<int, array{flat_no: string, member_mobile?: string|null, bill_month: string, bill_date: string, due_date: string, charge_head: string, amount: float|string, notes?: string|null}>  $rows
     * @return Collection<int, MaintenanceBill>
     */
    public function createFromRows(Society $society, array $rows): Collection
    {
        $settings = $this->settings($society);
        $taxes = $this->activeTaxes($society);
        $bills = new Collection;

        $grouped = collect($rows)->groupBy(fn (array $row) => strtoupper(trim((string) $row['flat_no'])).'|'.trim((string) $row['bill_month']));

        DB::transaction(function () use ($society, $settings, $taxes, $grouped, &$bills) {
            foreach ($grouped as $group) {
                $first = $group->first();
                $unit = $this->findUnit($society, (string) $first['flat_no']);
                $member = $unit ? $this->memberFor($unit) : $this->findMember($society, (string) $first['flat_no'], $first['member_mobile'] ?? null);

                $lines = $group->values()->map(fn (array $row, int $i) => [
                    'charge_head_id' => ChargeHead::query()->forSociety($society)->where('name', trim((string) $row['charge_head']))->value('id'),
                    'charge_head_name' => trim((string) $row['charge_head']),
                    'description' => $row['notes'] ?? null,
                    'amount' => round((float) $row['amount'], 2),
                    'sort_order' => $i + 1,
                ])->all();

                $bills->push($this->createBill($society, [
                    'member' => $member,
                    'unit' => $unit,
                    'flat_number' => (string) $first['flat_no'],
                    'bill_month' => trim((string) $first['bill_month']),
                    'bill_date' => Carbon::parse($first['bill_date']),
                    'due_date' => Carbon::parse($first['due_date']),
                    'bill_cycle' => trim((string) $first['bill_month']),
                    'billing_type' => $settings->default_bill_type ?: 'Monthly Maintenance',
                    'lines' => $lines,
                    'taxes' => $taxes,
                    'include_previous_dues' => (bool) $settings->include_previous_dues,
                    'collection_account' => $settings->default_collection_account,
                    'notes' => null,
                    'send_email' => (bool) $settings->auto_email_bill,
                    'send_sms' => (bool) $settings->auto_sms_bill,
                ]));
            }
        });

        $this->queueDelivery($bills);

        return $bills;
    }

    /**
     * Persist a single bill + items from a prepared payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function createBill(Society $society, array $payload): MaintenanceBill
    {
        /** @var Member|null $member */
        $member = $payload['member'] ?? null;
        /** @var Unit|null $unit */
        $unit = $payload['unit'] ?? null;
        $lines = $payload['lines'];

        $subTotal = round(array_sum(array_column($lines, 'amount')), 2);
        $discount = round((float) ($payload['discount'] ?? 0), 2);
        $lateFee = round((float) ($payload['late_fee'] ?? 0), 2);
        $tax = $this->taxFor($subTotal, $payload['taxes'] ?? new Collection);
        $previousDues = ($payload['include_previous_dues'] ?? false)
            ? $this->outstandingBefore($society, $unit, $member)
            : 0.0;
        $total = round(max(0, $subTotal - $discount + $lateFee + $tax + $previousDues), 2);

        $bill = MaintenanceBill::create([
            'society_id' => $society->id,
            'bill_number' => $this->nextBillNumber($society),
            'member_id' => $member?->id,
            'unit_id' => $unit?->id,
            'member_name' => $payload['member_name'] ?? $member?->name ?? $unit?->occupied_by_name ?? $unit?->owner_name,
            'flat_number' => $payload['flat_number'] ?? $unit?->unit_number ?? $member?->flat_unit,
            'tower_wing' => $payload['tower_wing'] ?? $unit?->building ?? $unit?->wing ?? $member?->tower_wing,
            'floor' => $payload['floor'] ?? $unit?->floor,
            'bill_month' => $payload['bill_month'],
            'bill_date' => $payload['bill_date'],
            'due_date' => $payload['due_date'],
            'bill_cycle' => $payload['bill_cycle'] ?? $payload['bill_month'],
            'billing_type' => $payload['billing_type'] ?? 'Monthly Maintenance',
            'sub_total' => $subTotal,
            'discount' => $discount,
            'late_fee' => $lateFee,
            'tax_amount' => $tax,
            'previous_dues' => $previousDues,
            'total_amount' => $total,
            'collected_amount' => 0,
            'outstanding_amount' => $total,
            'status' => 'pending',
            'collection_account' => $payload['collection_account'] ?? null,
            'payment_mode' => $payload['payment_mode'] ?? null,
            'reference_no' => $payload['reference_no'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'send_email' => (bool) ($payload['send_email'] ?? false),
            'send_sms' => (bool) ($payload['send_sms'] ?? false),
            'send_whatsapp' => (bool) ($payload['send_whatsapp'] ?? false),
        ]);

        foreach (array_values($lines) as $index => $line) {
            $bill->items()->create([
                'charge_head_id' => $line['charge_head_id'] ?? null,
                'charge_head_name' => $line['charge_head_name'],
                'description' => $line['description'] ?? null,
                'amount' => round((float) $line['amount'], 2),
                'sort_order' => $line['sort_order'] ?? $index + 1,
            ]);
        }

        return $bill;
    }

    /**
     * Flip pending / partial bills past their due date to overdue.
     */
    public function markOverdue(?Carbon $asOf = null): int
    {
        $asOf ??= Carbon::today();

        return MaintenanceBill::query()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', $asOf->toDateString())
            ->where('outstanding_amount', '>', 0)
            ->update(['status' => 'overdue', 'updated_at' => now()]);
    }

    /**
     * Add the configured late fee to each overdue bill once its grace period
     * has lapsed. Applied at most once per bill.
     */
    public function applyLateFees(?Carbon $asOf = null): int
    {
        $asOf ??= Carbon::today();
        $applied = 0;

        LateFeeSetting::query()->where('enable_late_fee', true)->whereNotNull('society_id')->each(function (LateFeeSetting $setting) use ($asOf, &$applied) {
            $cutoff = $asOf->copy()->subDays((int) $setting->grace_period_days);

            MaintenanceBill::query()
                ->where('society_id', $setting->society_id)
                ->whereIn('status', ['overdue', 'pending', 'partial'])
                ->where('outstanding_amount', '>', 0)
                ->whereNull('late_fee_applied_at')
                ->whereDate('due_date', '<', $cutoff->toDateString())
                ->when($setting->exempt_bill_types, fn ($q) => $q->whereNotIn('billing_type', $setting->exempt_bill_types))
                ->when($setting->exempt_members, fn ($q) => $q->where(fn ($s) => $s->whereNull('member_id')->orWhereNotIn('member_id', $setting->exempt_members)))
                ->when($setting->exempt_charge_heads, fn ($q) => $q->with('items'))
                ->orderBy('id')
                ->each(function (MaintenanceBill $bill) use ($setting, $asOf, &$applied) {
                    $fee = $this->lateFeeAmount($bill, $setting, $asOf);
                    if ($fee <= 0) {
                        return;
                    }

                    $bill->forceFill([
                        'late_fee' => round((float) $bill->late_fee + $fee, 2),
                        'total_amount' => round((float) $bill->total_amount + $fee, 2),
                        'outstanding_amount' => round((float) $bill->outstanding_amount + $fee, 2),
                        'late_fee_applied_at' => now(),
                        'status' => 'overdue',
                    ])->save();
                    $applied++;
                });
        });

        return $applied;
    }

    /**
     * Recompute collected / outstanding / status from the bill's payments.
     */
    public function recalculate(MaintenanceBill $bill): MaintenanceBill
    {
        $collected = (float) $bill->payments()->whereIn('status', ['paid', 'partial'])->sum('paid_amount');
        $outstanding = max(0, round((float) $bill->total_amount - $collected, 2));

        $status = match (true) {
            $bill->status === 'cancelled' => 'cancelled',
            $outstanding <= 0 => 'paid',
            $collected > 0 => 'partial',
            $bill->due_date && $bill->due_date->lt(Carbon::today()) => 'overdue',
            default => 'pending',
        };

        $bill->forceFill([
            'collected_amount' => round($collected, 2),
            'outstanding_amount' => $outstanding,
            'status' => $status,
        ])->save();

        return $bill;
    }

    public function nextBillNumber(Society $society): string
    {
        $series = NumberingSeries::query()
            ->forSociety($society)
            ->where('document_type', 'maintenance_bill')
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->lockForUpdate()
            ->first();

        if ($series) {
            return $series->generateNext();
        }

        $settings = $this->settings($society);
        $prefix = $settings->bill_number_prefix ?: 'MB';
        $last = (int) MaintenanceBill::query()->forSociety($society)->max('id');

        return $prefix.'-'.now()->format('Y').'-'.str_pad((string) ($last + 1), 6, '0', STR_PAD_LEFT);
    }

    public function settings(Society $society): BillSetting
    {
        return BillSetting::query()->where('society_id', $society->id)->first() ?? new BillSetting(['society_id' => $society->id]);
    }

    /**
     * Total still outstanding on earlier bills for the same unit / member.
     */
    public function outstandingBefore(Society $society, ?Unit $unit, ?Member $member): float
    {
        if (! $unit && ! $member) {
            return 0.0;
        }

        return round((float) MaintenanceBill::query()
            ->forSociety($society)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where(function ($q) use ($unit, $member) {
                if ($unit) {
                    $q->where('unit_id', $unit->id);
                }
                if ($member) {
                    $unit ? $q->orWhere('member_id', $member->id) : $q->where('member_id', $member->id);
                }
            })
            ->sum('outstanding_amount'), 2);
    }

    /**
     * Resolve the member for a unit: explicit link first, then flat-number match.
     */
    public function memberFor(Unit $unit): ?Member
    {
        if ($unit->member_id) {
            return $unit->member;
        }

        return Member::query()
            ->where('society_id', $unit->society_id)
            ->whereRaw('UPPER(flat_unit) = ?', [strtoupper((string) $unit->unit_number)])
            ->orderByRaw("CASE member_type WHEN 'owner' THEN 0 WHEN 'tenant' THEN 1 ELSE 2 END")
            ->first();
    }

    /**
     * @param  Collection<int, MaintenanceBill>  $bills
     */
    private function queueDelivery(Collection $bills): void
    {
        foreach ($bills as $bill) {
            if ($bill->send_email || $bill->send_sms || $bill->send_whatsapp) {
                SendMaintenanceBill::dispatch($bill);
            }
        }
    }

    /**
     * @return Collection<int, ChargeHead>
     */
    private function chargeHeads(Society $society, array $ids): Collection
    {
        return ChargeHead::query()
            ->forSociety($society)
            ->where('status', 'active')
            ->whereIn('id', $ids)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, Tax>
     */
    private function activeTaxes(Society $society): Collection
    {
        return Tax::query()->forSociety($society)->where('status', 'active')->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Unit>
     */
    private function billableUnits(Society $society, ?array $unitIds): Collection
    {
        return Unit::query()
            ->forSociety($society)
            ->where('status', 'occupied')
            ->when($unitIds, fn ($q) => $q->whereIn('id', $unitIds))
            ->orderBy('building')
            ->orderBy('unit_number')
            ->get();
    }

    /**
     * @return Collection<int, int>
     */
    private function unitsBilledFor(Society $society, string $billMonth): Collection
    {
        return MaintenanceBill::query()
            ->forSociety($society)
            ->where('bill_month', $billMonth)
            ->whereNotNull('unit_id')
            ->pluck('unit_id');
    }

    /**
     * One line per charge head, priced for the unit.
     *
     * @param  Collection<int, ChargeHead>  $heads
     * @return array<int, array{charge_head_id: int, charge_head_name: string, description: ?string, amount: float, sort_order: int}>
     */
    private function linesFor(Unit $unit, Collection $heads): array
    {
        $lines = [];
        foreach ($heads->values() as $i => $head) {
            $amount = match ($head->calculation_type) {
                'per_sqft' => (float) $head->default_amount * (float) ($unit->area_sqft ?? 0),
                default => (float) $head->default_amount,
            };

            $lines[] = [
                'charge_head_id' => $head->id,
                'charge_head_name' => $head->name,
                'description' => $head->description,
                'amount' => round($amount, 2),
                'sort_order' => $i + 1,
            ];
        }

        return $lines;
    }

    /**
     * Total tax for a sub-total: percentage taxes on the base, fixed taxes added.
     *
     * @param  Collection<int, Tax>  $taxes
     */
    private function taxFor(float $subTotal, Collection $taxes): float
    {
        $total = 0.0;
        foreach ($taxes as $tax) {
            if ($tax->slab_from !== null && $subTotal < (float) $tax->slab_from) {
                continue;
            }
            if ($tax->slab_to !== null && (float) $tax->slab_to > 0 && $subTotal > (float) $tax->slab_to) {
                continue;
            }
            $total += $tax->tax_type === 'fixed' ? (float) $tax->rate : $subTotal * (float) $tax->rate / 100;
        }

        return round($total, 2);
    }

    private function lateFeeAmount(MaintenanceBill $bill, LateFeeSetting $setting, Carbon $asOf): float
    {
        $base = (float) $bill->outstanding_amount;

        $exemptHeads = array_map('intval', (array) $setting->exempt_charge_heads);
        if ($exemptHeads !== [] && $bill->relationLoaded('items') && $bill->items->isNotEmpty()) {
            $itemsTotal = (float) $bill->items->sum('amount');
            $exemptTotal = (float) $bill->items->whereIn('charge_head_id', $exemptHeads)->sum('amount');

            if ($itemsTotal <= 0 || $exemptTotal >= $itemsTotal) {
                return 0.0;
            }

            $base *= 1 - $exemptTotal / $itemsTotal;
        }

        $daysLate = max(0, (int) $bill->due_date->copy()->addDays((int) $setting->grace_period_days)->diffInDays($asOf, false));

        $fee = match ($setting->late_fee_type) {
            'flat' => (float) ($setting->late_fee_flat ?? 0),
            'per_day' => (float) ($setting->late_fee_per_day ?? 0) * $daysLate,
            default => $base * (float) ($setting->late_fee_percent ?? 0) / 100,
        };

        if ($setting->max_late_fee_cap !== null && (float) $setting->max_late_fee_cap > 0) {
            $fee = min($fee, (float) $setting->max_late_fee_cap);
        }

        return round($fee, 2);
    }

    private function findUnit(Society $society, string $flatNo): ?Unit
    {
        return Unit::query()
            ->forSociety($society)
            ->whereRaw('UPPER(unit_number) = ?', [strtoupper(trim($flatNo))])
            ->first();
    }

    private function findMember(Society $society, string $flatNo, ?string $mobile): ?Member
    {
        return Member::query()
            ->forSociety($society)
            ->where(function ($q) use ($flatNo, $mobile) {
                $q->whereRaw('UPPER(flat_unit) = ?', [strtoupper(trim($flatNo))]);
                if ($mobile) {
                    $q->orWhere('mobile', 'like', '%'.preg_replace('/\D+/', '', $mobile).'%');
                }
            })
            ->first();
    }
}
