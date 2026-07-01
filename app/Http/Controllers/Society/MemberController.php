<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Society;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $society = Society::firstOrFail();

        $query = $society->members()->latest('join_date');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('flat_unit', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tower = $request->input('tower')) {
            $query->where('tower_wing', $tower);
        }

        if ($unit = $request->input('unit')) {
            $query->where('flat_unit', $unit);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->where('member_type', $type);
        }

        $members = $query->paginate(8)->withQueryString();

        $stats = [
            'total' => $society->members()->count(),
            'active' => $society->members()->where('status', 'active')->count(),
            'inactive' => $society->members()->where('status', 'inactive')->count(),
            'blocked' => $society->members()->where('status', 'blocked')->count(),
        ];

        $towers = $society->members()->whereNotNull('tower_wing')->distinct()->orderBy('tower_wing')->pluck('tower_wing');
        $units = $society->members()->whereNotNull('flat_unit')->distinct()->orderBy('flat_unit')->pluck('flat_unit');

        return view('society.members.index', compact('society', 'members', 'stats', 'towers', 'units'));
    }

    public function create()
    {
        $society = Society::firstOrFail();

        return view('society.members.create', compact('society'));
    }

    public function store(Request $request)
    {
        $society = Society::firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'member_type' => 'required|in:owner,family_member,tenant',
            'flat_unit' => 'nullable|string|max:50',
            'tower_wing' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,blocked',
            'join_date' => 'nullable|date',
        ]);

        $society->members()->create($validated);

        return redirect()->route('society.members.index')->with('success', 'Member added successfully.');
    }

    public function show(Member $member)
    {
        return view('society.members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('society.members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'member_type' => 'required|in:owner,family_member,tenant',
            'flat_unit' => 'nullable|string|max:50',
            'tower_wing' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,blocked',
            'join_date' => 'nullable|date',
        ]);

        $member->update($validated);

        return redirect()->route('society.members.index')->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('society.members.index')->with('success', 'Member deleted successfully.');
    }
}
