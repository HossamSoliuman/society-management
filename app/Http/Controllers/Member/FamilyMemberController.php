<?php

namespace App\Http\Controllers\Member;

use App\Http\Requests\StoreFamilyMemberRequest;
use App\Models\FamilyMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FamilyMemberController extends PortalController
{
    public function index(): View
    {
        $member = $this->currentMember();

        return view('member.family.index', [
            'member' => $member,
            'familyMembers' => $member->familyMembers()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('member.family.create', [
            'member' => $this->currentMember(),
            'relations' => FamilyMember::RELATIONS,
        ]);
    }

    public function store(StoreFamilyMemberRequest $request): RedirectResponse
    {
        $member = $this->currentMember();

        $member->familyMembers()->create($request->payload() + ['society_id' => $member->society_id]);

        return redirect()->route('member.family.index')->with('success', 'Family member added.');
    }

    public function edit(FamilyMember $familyMember): View
    {
        $this->ownedByMember($familyMember->member_id);

        return view('member.family.edit', [
            'member' => $this->currentMember(),
            'familyMember' => $familyMember,
            'relations' => FamilyMember::RELATIONS,
        ]);
    }

    public function update(StoreFamilyMemberRequest $request, FamilyMember $familyMember): RedirectResponse
    {
        $this->ownedByMember($familyMember->member_id);

        $familyMember->update($request->payload());

        return redirect()->route('member.family.index')->with('success', 'Family member updated.');
    }

    public function destroy(FamilyMember $familyMember): RedirectResponse
    {
        $this->ownedByMember($familyMember->member_id);

        $familyMember->delete();

        return redirect()->route('member.family.index')->with('success', 'Family member removed.');
    }
}
