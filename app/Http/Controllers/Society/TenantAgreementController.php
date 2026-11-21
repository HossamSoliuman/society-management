<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantAgreementRequest;
use App\Models\Tenant;
use App\Models\TenantAgreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantAgreementController extends Controller
{
    /** Filters for the agreements register. */
    private const FILTERS = ['all' => 'All', 'active' => 'Active', 'expiring' => 'Expiring Soon', 'expired' => 'Expired', 'terminated' => 'Terminated'];

    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $filter = array_key_exists((string) $request->query('filter'), self::FILTERS) ? (string) $request->query('filter') : 'all';
        $today = Carbon::today();
        $soon = $today->copy()->addDays(TenantAgreement::EXPIRING_WITHIN_DAYS);

        $agreements = TenantAgreement::query()
            ->forSociety($society)
            ->with(['tenant', 'unit'])
            ->when($filter === 'active', fn ($q) => $q->where('status', 'active')->whereDate('end_date', '>', $soon))
            ->when($filter === 'expiring', fn ($q) => $q->expiringWithin())
            ->when($filter === 'expired', fn ($q) => $q->lapsed())
            ->when($filter === 'terminated', fn ($q) => $q->where('status', 'terminated'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('agreement_number', 'like', "%{$term}%")
                    ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', "%{$term}%")));
            })
            ->orderBy('end_date')
            ->paginate(12)
            ->withQueryString();

        $base = TenantAgreement::query()->forSociety($society);

        return view('society.tenants.agreements', [
            'agreements' => $agreements,
            'filter' => $filter,
            'filters' => self::FILTERS,
            'stats' => [
                'active' => (clone $base)->where('status', 'active')->whereDate('end_date', '>=', $today)->count(),
                'expiring' => (clone $base)->expiringWithin()->count(),
                'expired' => (clone $base)->lapsed()->count(),
                'rent' => (float) (clone $base)->where('status', 'active')->whereDate('end_date', '>=', $today)->sum('monthly_rent'),
            ],
        ]);
    }

    public function store(StoreTenantAgreementRequest $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);
        $data = $request->validated();

        $tenant->agreements()->create([
            'society_id' => $tenant->society_id,
            'unit_id' => $tenant->unit_id,
            'agreement_number' => $data['agreement_number'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'monthly_rent' => $data['monthly_rent'],
            'security_deposit' => $data['security_deposit'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'document_path' => $request->hasFile('document')
                ? $request->file('document')->store("tenant-agreements/{$tenant->society_id}", 'local')
                : null,
            'status' => 'active',
        ]);

        return redirect()->route('society.tenants.show', $tenant)->with('success', 'Agreement added.');
    }

    public function terminate(TenantAgreement $agreement): RedirectResponse
    {
        abort_unless($agreement->status === 'active', 404);

        $agreement->update(['status' => 'terminated', 'terminated_at' => now()]);

        return back()->with('success', 'Agreement terminated.');
    }

    public function download(TenantAgreement $agreement): StreamedResponse
    {
        abort_unless($agreement->document_path && Storage::disk('local')->exists($agreement->document_path), 404);

        $name = 'agreement-'.($agreement->agreement_number ?: $agreement->id).'.'.pathinfo($agreement->document_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($agreement->document_path, $name);
    }

    public function destroy(TenantAgreement $agreement): RedirectResponse
    {
        if ($agreement->document_path) {
            Storage::disk('local')->delete($agreement->document_path);
        }
        $agreement->delete();

        return back()->with('success', 'Agreement deleted.');
    }
}
