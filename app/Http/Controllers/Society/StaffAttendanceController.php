<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffAttendanceController extends Controller
{
    /**
     * Monthly attendance grid (staff × days) with per-person totals.
     */
    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $month = $this->month($request);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $staff = Staff::query()
            ->forSociety($society)
            ->where(fn ($q) => $q->where('status', 'active')->orWhereHas('attendance', fn ($a) => $a->whereBetween('date', [$start, $end])))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->orderBy('role')
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'shift', 'status']);

        $records = StaffAttendance::query()
            ->forSociety($society)
            ->whereBetween('date', [$start, $end])
            ->whereIn('staff_id', $staff->pluck('id'))
            ->get(['staff_id', 'date', 'status'])
            ->groupBy('staff_id')
            ->map(fn ($rows) => $rows->keyBy(fn (StaffAttendance $a) => $a->date->day));

        return view('society.staff.attendance', [
            'staff' => $staff,
            'records' => $records,
            'month' => $month,
            'days' => range(1, $month->daysInMonth),
            'roles' => Staff::ROLES,
            'statuses' => StaffAttendance::STATUSES,
        ]);
    }

    /**
     * Daily sheet: one row per active staff member for the chosen date.
     */
    public function mark(Request $request): View
    {
        $society = $this->currentSociety();
        $date = $request->filled('date') ? Carbon::parse($request->string('date'))->startOfDay() : Carbon::today();
        abort_if($date->isFuture(), 404);

        $staff = Staff::query()->forSociety($society)->where('status', 'active')->orderBy('shift')->orderBy('name')->get();
        $existing = StaffAttendance::query()->forSociety($society)->whereDate('date', $date)->get()->keyBy('staff_id');

        return view('society.staff.mark', [
            'staff' => $staff,
            'existing' => $existing,
            'date' => $date,
            'statuses' => StaffAttendance::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $staffIds = Staff::query()->forSociety($society)->pluck('id')->all();

        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'attendance' => ['required', 'array'],
            'attendance.*.status' => ['nullable', Rule::in(array_keys(StaffAttendance::STATUSES))],
            'attendance.*.check_in' => ['nullable', 'date_format:H:i'],
            'attendance.*.check_out' => ['nullable', 'date_format:H:i'],
            'attendance.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $date = Carbon::parse($data['date'])->toDateString();
        $saved = 0;

        DB::transaction(function () use ($data, $staffIds, $society, $date, $request, &$saved) {
            foreach ($data['attendance'] as $staffId => $row) {
                if (! in_array((int) $staffId, $staffIds, true)) {
                    continue;
                }

                if (empty($row['status'])) {
                    StaffAttendance::query()->where('staff_id', $staffId)->whereDate('date', $date)->delete();

                    continue;
                }

                $attributes = [
                    'society_id' => $society->id,
                    'status' => $row['status'],
                    'check_in' => in_array($row['status'], ['present', 'half_day'], true) ? ($row['check_in'] ?? null) : null,
                    'check_out' => in_array($row['status'], ['present', 'half_day'], true) ? ($row['check_out'] ?? null) : null,
                    'notes' => $row['notes'] ?? null,
                    'marked_by' => $request->user()->id,
                ];

                $record = StaffAttendance::query()->where('staff_id', $staffId)->whereDate('date', $date)->first();
                $record
                    ? $record->update($attributes)
                    : StaffAttendance::create($attributes + ['staff_id' => (int) $staffId, 'date' => $date]);
                $saved++;
            }
        });

        return redirect()->route('society.staff.attendance.mark', ['date' => $date])
            ->with('success', "Attendance saved for {$saved} staff member(s) on ".Carbon::parse($date)->format('d M Y').'.');
    }

    private function month(Request $request): Carbon
    {
        $value = (string) $request->query('month');

        return preg_match('/^\d{4}-\d{2}$/', $value) ? Carbon::createFromFormat('Y-m', $value)->startOfMonth() : Carbon::today()->startOfMonth();
    }
}
