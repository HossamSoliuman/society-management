<?php

namespace App\Http\Controllers\Member;

use App\Http\Requests\StoreVisitorPreApprovalRequest;
use App\Models\Visitor;
use App\Models\VisitorPreApproval;
use App\Services\VisitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Residents pre-approve guests (the gate gets a pass code) and see who
 * visited their unit.
 */
class VisitorController extends PortalController
{
    public function __construct(private readonly VisitorService $visitors) {}

    public function index(): View
    {
        $member = $this->currentMember()->load('society');
        $unit = $member->primaryUnit();

        return view('member.visitors.index', [
            'member' => $member,
            'unit' => $unit,
            'canPreApprove' => $unit && $member->society?->setting('portal_visitor_preapproval_enabled'),
            'purposes' => Visitor::PURPOSES,
            'passes' => VisitorPreApproval::query()
                ->where('society_id', $member->society_id)
                ->where('member_id', $member->id)
                ->latest('expected_at')
                ->limit(20)
                ->get(),
            'visits' => $unit
                ? Visitor::query()->where('society_id', $member->society_id)->where('unit_id', $unit->id)->latest('check_in_at')->limit(20)->get()
                : collect(),
        ]);
    }

    public function store(StoreVisitorPreApprovalRequest $request): RedirectResponse
    {
        $member = $this->currentMember()->load('society');
        $unit = $member->primaryUnit();

        if (! $unit || ! $member->society?->setting('portal_visitor_preapproval_enabled')) {
            return redirect()->route('member.visitors.index')->with('error', 'Visitor passes are not available for your account. Please contact the society office.');
        }

        $pass = $this->visitors->preApprove($member->society, $unit, $member, $request->validated(), $request->user());

        return redirect()->route('member.visitors.index')
            ->with('success', "Pass created. Share code {$pass->pass_code} with {$pass->visitor_name}.");
    }

    public function cancel(VisitorPreApproval $preApproval): RedirectResponse
    {
        $this->ownedByMember($preApproval->member_id);
        abort_unless($preApproval->status === 'pending', 404);

        $preApproval->update(['status' => 'cancelled']);

        return redirect()->route('member.visitors.index')->with('success', 'Pass cancelled.');
    }
}
