<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Inbox for demo requests submitted from the public marketing site.
 */
class DemoRequestController extends Controller
{
    public function index(Request $request): View
    {
        $demoRequests = DemoRequest::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(fn ($sub) => $sub
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('society_name', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = DemoRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('superadmin.demo-requests.index', [
            'demoRequests' => $demoRequests,
            'counts' => $counts,
            'statuses' => DemoRequest::STATUSES,
        ]);
    }

    public function show(DemoRequest $demoRequest): View
    {
        $demoRequest->load('handler');

        return view('superadmin.demo-requests.show', [
            'demoRequest' => $demoRequest,
            'statuses' => DemoRequest::STATUSES,
        ]);
    }

    public function update(Request $request, DemoRequest $demoRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(DemoRequest::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $demoRequest->update($validated + [
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return redirect()->route('superadmin.demo-requests.show', $demoRequest)->with('success', 'Demo request updated.');
    }

    public function destroy(DemoRequest $demoRequest): RedirectResponse
    {
        $demoRequest->delete();

        return redirect()->route('superadmin.demo-requests.index')->with('success', 'Demo request deleted.');
    }
}
