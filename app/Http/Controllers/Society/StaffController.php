<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Models\ServiceVendor;
use App\Models\Society;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $status = array_key_exists((string) $request->query('status'), Staff::STATUSES) ? (string) $request->query('status') : 'active';
        $today = Carbon::today();

        $staff = Staff::query()
            ->forSociety($society)
            ->with(['vendor', 'attendance' => fn ($q) => $q->whereDate('date', $today)])
            ->where('status', $status)
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->string('shift')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhere('employee_code', 'like', "%{$term}%"));
            })
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $todayCounts = StaffAttendance::query()
            ->forSociety($society)
            ->whereDate('date', $today)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
        $active = Staff::query()->forSociety($society)->where('status', 'active')->count();

        return view('society.staff.index', [
            'staff' => $staff,
            'status' => $status,
            'roles' => Staff::ROLES,
            'shifts' => Staff::SHIFTS,
            'statuses' => Staff::STATUSES,
            'stats' => [
                'active' => $active,
                'present' => (int) (($todayCounts['present'] ?? 0) + ($todayCounts['half_day'] ?? 0)),
                'absent' => (int) ($todayCounts['absent'] ?? 0),
                'leave' => (int) ($todayCounts['leave'] ?? 0),
                'unmarked' => max(0, $active - (int) $todayCounts->sum()),
                'payroll' => (float) Staff::query()->forSociety($society)->where('status', 'active')->where('employment_type', 'society')->sum('monthly_salary'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('society.staff.create', $this->formData($this->currentSociety()) + [
            'employee' => new Staff(['employment_type' => 'society', 'shift' => 'general', 'status' => 'active', 'joining_date' => Carbon::today()]),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();
        if ($data['employment_type'] !== 'agency') {
            $data['vendor_id'] = null;
        }

        $staff = Staff::create($data + ['society_id' => $society->id]);
        if (! $staff->employee_code) {
            $staff->update(['employee_code' => 'EMP-'.str_pad((string) $staff->id, 4, '0', STR_PAD_LEFT)]);
        }

        return redirect()->route('society.staff.show', $staff)->with('success', "{$staff->name} added to staff.");
    }

    public function show(Request $request, Staff $staff): View
    {
        $month = $this->month($request);

        $records = $staff->attendance()
            ->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn (StaffAttendance $a) => $a->date->day);

        return view('society.staff.show', [
            'employee' => $staff->load('vendor'),
            'month' => $month,
            'records' => $records,
            'summary' => collect(StaffAttendance::STATUSES)->map(fn ($meta, $key) => $records->where('status', $key)->count()),
        ]);
    }

    public function edit(Staff $staff): View
    {
        return view('society.staff.edit', $this->formData($this->currentSociety()) + ['employee' => $staff]);
    }

    public function update(StoreStaffRequest $request, Staff $staff): RedirectResponse
    {
        $data = $request->validated();
        if ($data['employment_type'] !== 'agency') {
            $data['vendor_id'] = null;
        }
        $data['employee_code'] = ($data['employee_code'] ?? null) ?: $staff->employee_code;

        $staff->update($data);

        return redirect()->route('society.staff.show', $staff)->with('success', 'Staff details updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->delete();

        return redirect()->route('society.staff.index')->with('success', "{$staff->name} removed from staff.");
    }

    private function month(Request $request): Carbon
    {
        $value = (string) $request->query('month');

        return preg_match('/^\d{4}-\d{2}$/', $value) ? Carbon::createFromFormat('Y-m', $value)->startOfMonth() : Carbon::today()->startOfMonth();
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Society $society): array
    {
        return [
            'roles' => Staff::ROLES,
            'shifts' => Staff::SHIFTS,
            'employmentTypes' => Staff::EMPLOYMENT_TYPES,
            'statuses' => Staff::STATUSES,
            'idProofs' => Tenant::ID_PROOFS,
            'vendors' => ServiceVendor::query()->where('society_id', $society->id)->orderBy('name')->get(['id', 'name', 'category']),
        ];
    }
}
