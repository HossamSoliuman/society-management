<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Society;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorPreApproval;
use App\Notifications\VisitorArrived;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Gate workflow: resident or office pre-approvals (pass codes), walk-in and
 * pass check-ins, check-outs, and the "your visitor has arrived" alert.
 */
class VisitorService
{
    /**
     * @param  array<string, mixed>  $data  visitor_name, mobile, purpose, people_count, vehicle_no, expected_at, notes
     */
    public function preApprove(Society $society, Unit $unit, ?Member $member, array $data, User $createdBy): VisitorPreApproval
    {
        $expectedAt = Carbon::parse($data['expected_at']);
        $validHours = max(1, (int) $society->setting('visitor_pass_validity_hours'));

        return VisitorPreApproval::create([
            'society_id' => $society->id,
            'unit_id' => $unit->id,
            'member_id' => $member?->id,
            'created_by' => $createdBy->id,
            'visitor_name' => $data['visitor_name'],
            'mobile' => $data['mobile'] ?? null,
            'purpose' => $data['purpose'],
            'people_count' => $data['people_count'] ?? 1,
            'vehicle_no' => isset($data['vehicle_no']) ? strtoupper($data['vehicle_no']) : null,
            'expected_at' => $expectedAt,
            'valid_until' => $expectedAt->copy()->addHours($validHours),
            'pass_code' => VisitorPreApproval::freshCode($society->id),
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Record a visitor entering. With a pass, the pass is consumed and its
     * details carried over.
     *
     * @param  array<string, mixed>  $data  name, mobile, purpose, company, unit_id, people_count, vehicle_no, notes
     */
    public function checkIn(Society $society, array $data, User $guard, ?VisitorPreApproval $pass = null): Visitor
    {
        $visitor = DB::transaction(function () use ($society, $data, $guard, $pass): Visitor {
            $visitor = Visitor::create([
                'society_id' => $society->id,
                'unit_id' => $pass?->unit_id ?? $data['unit_id'],
                'member_id' => $pass?->member_id,
                'pre_approval_id' => $pass?->id,
                'name' => $pass?->visitor_name ?? $data['name'],
                'mobile' => $pass?->mobile ?? ($data['mobile'] ?? null),
                'purpose' => $pass?->purpose ?? $data['purpose'],
                'company' => $data['company'] ?? null,
                'people_count' => $pass?->people_count ?? ($data['people_count'] ?? 1),
                'vehicle_no' => ($pass?->vehicle_no ?? ($data['vehicle_no'] ?? null)) ? strtoupper((string) ($pass?->vehicle_no ?? $data['vehicle_no'])) : null,
                'check_in_at' => now(),
                'checked_in_by' => $guard->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $pass?->update(['status' => 'checked_in']);

            return $visitor;
        });

        foreach ($this->residentUsers($visitor) as $user) {
            $user->notify(new VisitorArrived($visitor));
        }

        return $visitor;
    }

    public function checkOut(Visitor $visitor, User $guard): Visitor
    {
        if ($visitor->isInside()) {
            $visitor->update(['check_out_at' => now(), 'checked_out_by' => $guard->id]);
        }

        return $visitor;
    }

    /**
     * Portal logins of the residents of the visited unit (or the resident
     * who issued the pass).
     *
     * @return Collection<int, User>
     */
    private function residentUsers(Visitor $visitor): Collection
    {
        $unitNumber = $visitor->unit?->unit_number;

        return Member::query()
            ->where('society_id', $visitor->society_id)
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->where(function ($q) use ($visitor, $unitNumber) {
                $q->when($visitor->member_id, fn ($m) => $m->orWhere('id', $visitor->member_id))
                    ->when($visitor->unit_id, fn ($m) => $m->orWhereHas('units', fn ($u) => $u->whereKey($visitor->unit_id)))
                    ->when($unitNumber, fn ($m) => $m->orWhere('flat_unit', $unitNumber));
            })
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(fn (?User $user) => $user && $user->status === 'active')
            ->unique('id')
            ->values();
    }
}
