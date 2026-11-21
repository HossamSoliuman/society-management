<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFamilyMemberRequest;
use App\Models\FamilyMember;
use App\Models\Member;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Society-wide register of residents' family members (residents also manage
 * their own household from the portal).
 */
class FamilyMemberController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $family = FamilyMember::query()
            ->forSociety($society)
            ->with('member')
            ->when($request->filled('relation'), fn ($q) => $q->where('relation', $request->string('relation')))
            ->when($request->query('resident') === 'yes', fn ($q) => $q->where('is_resident', true))
            ->when($request->query('resident') === 'no', fn ($q) => $q->where('is_resident', false))
            ->when($request->filled('member'), fn ($q) => $q->where('member_id', $request->integer('member')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhereHas('member', fn ($m) => $m->where('name', 'like', "%{$term}%")->orWhere('flat_unit', 'like', "%{$term}%")));
            })
            ->orderBy('member_id')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $base = FamilyMember::query()->forSociety($society);

        return view('society.family-members.index', [
            'family' => $family,
            'relations' => FamilyMember::RELATIONS,
            'stats' => [
                'total' => (clone $base)->count(),
                'residents' => (clone $base)->where('is_resident', true)->count(),
                'households' => (clone $base)->distinct()->count('member_id'),
                'children' => (clone $base)->whereNotNull('date_of_birth')->where('date_of_birth', '>', now()->subYears(18))->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('society.family-members.form', $this->formData($this->currentSociety()) + [
            'familyMember' => new FamilyMember(['member_id' => $request->integer('member') ?: null, 'is_resident' => true]),
        ]);
    }

    public function store(StoreFamilyMemberRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $familyMember = FamilyMember::create($request->payload() + ['society_id' => $society->id]);

        return redirect()->route('society.family-members.index')->with('success', "{$familyMember->name} added to {$familyMember->member?->name}'s household.");
    }

    public function edit(FamilyMember $familyMember): View
    {
        return view('society.family-members.form', $this->formData($this->currentSociety()) + ['familyMember' => $familyMember]);
    }

    public function update(StoreFamilyMemberRequest $request, FamilyMember $familyMember): RedirectResponse
    {
        $familyMember->update($request->payload());

        return redirect()->route('society.family-members.index')->with('success', "{$familyMember->name} updated.");
    }

    public function destroy(FamilyMember $familyMember): RedirectResponse
    {
        $familyMember->delete();

        return redirect()->route('society.family-members.index')->with('success', "{$familyMember->name} removed.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Society $society): array
    {
        return [
            'relations' => FamilyMember::RELATIONS,
            'members' => Member::query()->forSociety($society)->where('status', 'active')->whereIn('member_type', ['owner', 'tenant'])->orderBy('name')->get(['id', 'name', 'flat_unit']),
        ];
    }
}
