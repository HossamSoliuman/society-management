<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorPreApprovalRequest;
use App\Models\Unit;
use App\Models\Visitor;
use App\Models\VisitorPreApproval;
use App\Services\VisitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Expected visitors with pass codes; the gate checks a code to let them in.
 */
class VisitorPreApprovalController extends Controller
{
    private const VIEWS = ['upcoming' => 'Expected', 'arrived' => 'Arrived', 'closed' => 'Expired / Cancelled'];

    public function __construct(private readonly VisitorService $visitors) {}

    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $view = array_key_exists((string) $request->query('view'), self::VIEWS) ? (string) $request->query('view') : 'upcoming';

        $passes = VisitorPreApproval::query()
            ->forSociety($society)
            ->with(['unit', 'member', 'creator'])
            ->when($view === 'upcoming', fn ($q) => $q->usable()->orderBy('expected_at'))
            ->when($view === 'arrived', fn ($q) => $q->where('status', 'checked_in')->latest('updated_at'))
            ->when($view === 'closed', fn ($q) => $q->where(fn ($s) => $s->whereIn('status', ['cancelled', 'expired'])
                ->orWhere(fn ($p) => $p->where('status', 'pending')->where('valid_until', '<', now())))->latest('expected_at'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('visitor_name', 'like', "%{$term}%")
                    ->orWhere('pass_code', $term)
                    ->orWhere('mobile', 'like', "%{$term}%"));
            })
            ->paginate(15)
            ->withQueryString();

        return view('society.visitors.pre-approvals', [
            'passes' => $passes,
            'view' => $view,
            'views' => self::VIEWS,
            'units' => Unit::query()->forSociety($society)->orderBy('wing')->orderBy('unit_number')->get(['id', 'unit_number', 'wing']),
            'purposes' => Visitor::PURPOSES,
            'expectedToday' => VisitorPreApproval::query()->forSociety($society)->usable()
                ->whereBetween('expected_at', [Carbon::today(), Carbon::today()->endOfDay()])->count(),
        ]);
    }

    public function store(StoreVisitorPreApprovalRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();
        $unit = Unit::query()->forSociety($society)->findOrFail($data['unit_id']);

        $pass = $this->visitors->preApprove($society, $unit, null, $data, $request->user());

        return redirect()->route('society.visitors.pre-approvals')
            ->with('success', "Pass {$pass->pass_code} created for {$pass->visitor_name}.");
    }

    /**
     * Gate check: a valid pass code checks the visitor straight in.
     */
    public function verify(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validate(['pass_code' => ['required', 'digits:6']]);

        $pass = VisitorPreApproval::query()->forSociety($society)->usable()->where('pass_code', $data['pass_code'])->first();

        if (! $pass) {
            return back()->withInput()->with('error', 'No valid pass matches that code. It may be used, cancelled or expired.');
        }

        $visitor = $this->visitors->checkIn($society, [], $request->user(), $pass);

        return redirect()->route('society.visitors.index')
            ->with('success', "{$visitor->name} checked in with pass {$pass->pass_code} for {$visitor->unit?->unit_number}.");
    }

    public function checkIn(Request $request, VisitorPreApproval $preApproval): RedirectResponse
    {
        abort_unless($preApproval->status === 'pending' && $preApproval->valid_until->isFuture(), 404);

        $visitor = $this->visitors->checkIn($this->currentSociety(), [], $request->user(), $preApproval);

        return redirect()->route('society.visitors.index')->with('success', "{$visitor->name} checked in.");
    }

    public function cancel(VisitorPreApproval $preApproval): RedirectResponse
    {
        abort_unless($preApproval->status === 'pending', 404);

        $preApproval->update(['status' => 'cancelled']);

        return back()->with('success', "Pass for {$preApproval->visitor_name} cancelled.");
    }
}
