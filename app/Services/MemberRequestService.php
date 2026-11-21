<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Turns an approved join / ownership-transfer request into member records
 * and keeps the unit's owner details in step.
 */
class MemberRequestService
{
    public function approve(MemberRequest $request, User $by, ?Unit $unit = null, ?string $note = null): Member
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This request has already been reviewed.']);
        }

        $unit ??= $request->unit
            ?? Unit::query()->where('society_id', $request->society_id)->where('unit_number', $request->unit_number)->first();

        if (! $unit) {
            throw ValidationException::withMessages(['unit_id' => "Pick the unit for {$request->unit_number} before approving."]);
        }

        return DB::transaction(function () use ($request, $by, $unit, $note): Member {
            $isOwner = $request->member_type === 'owner';

            if ($request->request_type === 'transfer') {
                Member::query()
                    ->where('society_id', $request->society_id)
                    ->where('member_type', 'owner')
                    ->where('status', 'active')
                    ->where(fn ($q) => $q->where('flat_unit', $unit->unit_number)->orWhereKey($unit->member_id))
                    ->update(['status' => 'inactive']);
            }

            $member = Member::create([
                'society_id' => $request->society_id,
                'name' => $request->name,
                'member_type' => $request->request_type === 'transfer' ? 'owner' : $request->member_type,
                'flat_unit' => $unit->unit_number,
                'tower_wing' => $unit->wing ?? $unit->building,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'status' => 'active',
                'join_date' => $request->move_in_date ?? Carbon::today(),
            ]);

            if ($isOwner || $request->request_type === 'transfer') {
                $unit->update([
                    'member_id' => $member->id,
                    'owner_name' => $member->name,
                    'owner_mobile' => $member->mobile,
                ]);
            }

            if ($unit->status === 'vacant' && in_array($request->member_type, ['owner', 'tenant'], true)) {
                $unit->update([
                    'status' => 'occupied',
                    'occupied_by_name' => $member->name,
                    'occupied_by_role' => $request->member_type === 'tenant' ? 'Tenant' : 'Owner',
                ]);
            }

            $request->forceFill([
                'status' => 'approved',
                'unit_id' => $unit->id,
                'member_id' => $member->id,
                'reviewed_by' => $by->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ])->save();

            return $member;
        });
    }

    public function reject(MemberRequest $request, User $by, ?string $note): MemberRequest
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This request has already been reviewed.']);
        }

        $request->forceFill([
            'status' => 'rejected',
            'reviewed_by' => $by->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        return $request;
    }
}
