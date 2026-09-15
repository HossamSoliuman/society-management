<?php

namespace App\Services;

use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\NumberingSeries;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Records member payments and applies them to maintenance bills — either the
 * bill named on the payment or, when only a member/unit is known, oldest-first
 * across every open bill.
 */
class PaymentAllocationService
{
    /**
     * Allocations produced by the most recent record() call.
     *
     * @var array<int, array{bill_id: int, bill_number: string, amount: float}>
     */
    public array $lastAllocations = [];

    /**
     * Create a payment and allocate it; see $lastAllocations for what was applied.
     *
     * @param  array<string, mixed>  $data
     */
    public function record(Society $society, array $data, ?string $collectedBy = null): CollectionPayment
    {
        return DB::transaction(function () use ($society, $data, $collectedBy): CollectionPayment {
            $bill = ! empty($data['maintenance_bill_id']) ? MaintenanceBill::query()->forSociety($society)->find($data['maintenance_bill_id']) : null;
            $member = ! empty($data['member_id']) ? Member::query()->forSociety($society)->find($data['member_id']) : $bill?->member;
            $unit = ! empty($data['unit_id']) ? Unit::query()->forSociety($society)->find($data['unit_id']) : $bill?->unit;

            $paid = round((float) ($data['paid_amount'] ?? 0), 2);
            $discount = round((float) ($data['discount'] ?? 0), 2);
            $fine = round((float) ($data['fine_penalty'] ?? 0), 2);
            $totalDue = isset($data['total_due'])
                ? round((float) $data['total_due'], 2)
                : $this->outstandingFor($society, $bill, $member, $unit);
            $balance = max(0, round($totalDue - $discount - $paid, 2));

            $payment = CollectionPayment::create([
                'society_id' => $society->id,
                'receipt_number' => $this->nextReceiptNumber($society),
                'member_id' => $member?->id,
                'unit_id' => $unit?->id,
                'maintenance_bill_id' => $bill?->id,
                'member_name' => $data['member_name'] ?? $member?->name ?? $bill?->member_name,
                'member_mobile' => $member?->mobile,
                'member_email' => $member?->email,
                'flat_number' => $data['flat_number'] ?? $unit?->unit_number ?? $bill?->flat_number ?? $member?->flat_unit,
                'unit_label' => $data['unit_label'] ?? $unit?->building ?? $bill?->tower_wing,
                'unit_type' => $unit?->unit_type,
                'bill_type' => $data['bill_type'] ?? ($bill?->billing_type ?? 'Maintenance'),
                'bill_period' => $data['bill_period'] ?? $bill?->bill_month,
                'due_date' => $data['due_date'] ?? $bill?->due_date,
                'receipt_date' => isset($data['receipt_date']) ? Carbon::parse($data['receipt_date']) : now(),
                'total_due' => $totalDue,
                'paid_amount' => $paid,
                'discount' => $discount,
                'fine_penalty' => $fine,
                'balance_due' => $balance,
                'payment_mode' => $data['payment_mode'],
                'reference_no' => $data['reference_no'] ?? null,
                'transaction_utr' => $data['transaction_utr'] ?? null,
                'collected_by' => $collectedBy,
                'status' => match (true) {
                    $balance <= 0 => 'paid',
                    $paid > 0 => 'partial',
                    default => 'pending',
                },
                'is_online' => (bool) ($data['is_online'] ?? in_array($data['payment_mode'], ['upi', 'card', 'net_banking'], true)),
                'notes' => $data['notes'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);

            $this->lastAllocations = $this->allocate($payment, $bill, $member, $unit);

            return $payment;
        });
    }

    /**
     * Apply a payment's amount (+ discount) to bills. With an explicit bill only
     * that bill is touched; otherwise open bills for the member/unit are settled
     * oldest-first and any remainder is left unallocated on the payment.
     *
     * @return array<int, array{bill_id: int, bill_number: string, amount: float}>
     */
    public function allocate(CollectionPayment $payment, ?MaintenanceBill $bill, ?Member $member, ?Unit $unit): array
    {
        $available = round((float) $payment->paid_amount + (float) $payment->discount, 2);
        if ($available <= 0) {
            return [];
        }

        $bills = $bill
            ? new Collection([$bill])
            : $this->openBills($payment->society_id, $member, $unit);

        $allocations = [];
        foreach ($bills as $target) {
            if ($available <= 0) {
                break;
            }

            $amount = $bill ? $available : min($available, (float) $target->outstanding_amount);
            if ($amount <= 0) {
                continue;
            }

            $paidOnBill = $bill
                ? (float) $payment->paid_amount
                : min((float) $payment->paid_amount - array_sum(array_column($allocations, 'amount')), $amount);

            $target->forceFill([
                'collected_amount' => round((float) $target->collected_amount + $paidOnBill, 2),
                'outstanding_amount' => max(0, round((float) $target->outstanding_amount - $amount, 2)),
            ]);
            $target->status = (float) $target->outstanding_amount <= 0 ? 'paid' : 'partial';
            $target->save();

            if (! $bill && ! $payment->maintenance_bill_id) {
                // Point the receipt at the first bill it settled so receipts stay traceable.
                $payment->forceFill(['maintenance_bill_id' => $target->id, 'bill_period' => $target->bill_month])->saveQuietly();
            }

            $allocations[] = ['bill_id' => $target->id, 'bill_number' => $target->bill_number, 'amount' => round($amount, 2)];
            $available = round($available - $amount, 2);
        }

        return $allocations;
    }

    /**
     * Open bills for a member/unit, oldest due date first.
     *
     * @return Collection<int, MaintenanceBill>
     */
    public function openBills(int $societyId, ?Member $member, ?Unit $unit): Collection
    {
        if (! $member && ! $unit) {
            return new Collection;
        }

        return MaintenanceBill::query()
            ->where('society_id', $societyId)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('outstanding_amount', '>', 0)
            ->where(function ($q) use ($member, $unit) {
                if ($member) {
                    $q->where('member_id', $member->id);
                }
                if ($unit) {
                    $member ? $q->orWhere('unit_id', $unit->id) : $q->where('unit_id', $unit->id);
                }
            })
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }

    public function outstandingFor(Society $society, ?MaintenanceBill $bill, ?Member $member, ?Unit $unit): float
    {
        if ($bill) {
            return (float) $bill->outstanding_amount;
        }

        return round((float) $this->openBills($society->id, $member, $unit)->sum('outstanding_amount'), 2);
    }

    public function nextReceiptNumber(Society $society): string
    {
        $series = NumberingSeries::query()
            ->forSociety($society)
            ->where('document_type', 'receipt')
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->lockForUpdate()
            ->first();

        if ($series) {
            return $series->generateNext();
        }

        $last = (int) CollectionPayment::query()->forSociety($society)->max('id');

        return 'RCPT-'.now()->format('Y').'-'.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }
}
