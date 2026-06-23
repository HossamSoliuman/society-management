<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TermsCondition;

class TermsConditionController extends Controller
{
    public function index()
    {
        $terms = TermsCondition::with('creator')->latest()->paginate(10);
        return view('superadmin.terms.index', compact('terms'));
    }

    public function create()
    {
        return view('superadmin.terms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'document_type' => 'required|in:member_app,web_portal,other_documents',
            'applies_to' => 'required|string|max:255',
            'version' => 'required|string|max:10',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = auth()->id();

        TermsCondition::create($validated);
        return redirect()->route('superadmin.terms.index')->with('success', 'Terms & Conditions created');
    }

    public function edit(TermsCondition $term)
    {
        return view('superadmin.terms.edit', compact('term'));
    }

    public function update(Request $request, TermsCondition $term)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'document_type' => 'required|in:member_app,web_portal,other_documents',
            'applies_to' => 'required|string|max:255',
            'version' => 'required|string|max:10',
            'status' => 'required|in:active,inactive',
        ]);

        $term->update($validated);
        return redirect()->route('superadmin.terms.index')->with('success', 'Terms & Conditions updated');
    }

    public function destroy(TermsCondition $term)
    {
        $term->delete();
        return redirect()->route('superadmin.terms.index')->with('success', 'Terms & Conditions deleted');
    }
}
