<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorRequest;
use App\Models\Unit;
use App\Models\Visitor;
use App\Models\VisitorPreApproval;
use App\Services\VisitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Gate register: who is inside, today's log and history, walk-in check-in
 * and check-out.
 */
class VisitorController extends Controller
{
    private const VIEWS = ['inside' => 'Inside Now', 'today' => 'Today', 'all' => 'All Visits'];

    public function __construct(private readonly VisitorService $visitors) {}

    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $view = array_key_exists((string) $request->query('view'), self::VIEWS) ? (string) $request->query('view') : 'inside';
        $today = Carbon::today();

        $visits = Visitor::query()
            ->forSociety($society)
            ->with(['unit', 'preApproval'])
            ->when($view === 'inside', fn ($q) => $q->inside())
            ->when($view === 'today', fn ($q) => $q->where('check_in_at', '>=', $today))
            ->when($view === 'all' && $request->filled('from'), fn ($q) => $q->where('check_in_at', '>=', Carbon::parse($request->string('from'))->startOfDay()))
            ->when($view === 'all' && $request->filled('to'), fn ($q) => $q->where('check_in_at', '<=', Carbon::parse($request->string('to'))->endOfDay()))
            ->when($request->filled('purpose'), fn ($q) => $q->where('purpose', $request->string('purpose')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhere('vehicle_no', 'like', "%{$term}%")
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', "%{$term}%")));
            })
            ->latest('check_in_at')
            ->paginate(15)
            ->withQueryString();

        $todayBase = Visitor::query()->forSociety($society)->where('check_in_at', '>=', $today);

        return view('society.visitors.index', [
            'visits' => $visits,
            'view' => $view,
            'views' => self::VIEWS,
            'purposes' => Visitor::PURPOSES,
            'stats' => [
                'inside' => Visitor::query()->forSociety($society)->inside()->count(),
                'today' => (clone $todayBase)->count(),
                'deliveries' => (clone $todayBase)->where('purpose', 'delivery')->count(),
                'expected' => VisitorPreApproval::query()->forSociety($society)->usable()->whereBetween('expected_at', [$today, $today->copy()->endOfDay()])->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $society = $this->currentSociety();

        return view('society.visitors.create', [
            'units' => Unit::query()->forSociety($society)->orderBy('wing')->orderBy('unit_number')->get(['id', 'unit_number', 'wing', 'occupied_by_name']),
            'purposes' => Visitor::PURPOSES,
        ]);
    }

    public function store(StoreVisitorRequest $request): RedirectResponse
    {
        $visitor = $this->visitors->checkIn($this->currentSociety(), $request->validated(), $request->user());

        return redirect()->route('society.visitors.index')
            ->with('success', "{$visitor->name} checked in for {$visitor->unit?->unit_number}.");
    }

    public function checkout(Request $request, Visitor $visitor): RedirectResponse
    {
        $this->visitors->checkOut($visitor, $request->user());

        return back()->with('success', "{$visitor->name} checked out.");
    }
}
