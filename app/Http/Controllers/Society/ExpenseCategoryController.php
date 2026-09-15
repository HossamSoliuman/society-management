<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\ExpenseBudget;
use App\Models\ExpenseCategory;
use App\Services\ExpenseStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    /** Selectable icons for a category: fa-class => [label, tint color]. */
    private const ICONS = [
        'fa-screwdriver-wrench' => ['Maintenance', 'purple'],
        'fa-bolt' => ['Utilities', 'blue'],
        'fa-users' => ['Salary', 'green'],
        'fa-shield-halved' => ['Security', 'orange'],
        'fa-broom' => ['Cleaning', 'pink'],
        'fa-file-lines' => ['Admin Expenses', 'blue'],
        'fa-elevator' => ['Lift Maintenance', 'teal'],
        'fa-leaf' => ['Garden Maintenance', 'green'],
        'fa-bug' => ['Pest Control', 'blue'],
        'fa-wrench' => ['Repairs', 'yellow'],
        'fa-cart-shopping' => ['Purchase', 'teal'],
        'fa-tag' => ['General', 'gray'],
    ];

    public function __construct(private readonly ExpenseStatsService $stats) {}

    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $categories = ExpenseCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $base = ExpenseCategory::query()->when($society, fn ($q) => $q->where('society_id', $society->id));

        return view('society.expenses.categories.index', [
            'society' => $society,
            'categories' => $categories,
            'stats' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 'active')->count(),
                'inactive' => (clone $base)->where('status', 'inactive')->count(),
                'top' => $this->stats->byCategory($society, now()->startOfYear(), now()->endOfYear(), 1)[0]['label'] ?? '—',
            ],
            'usage' => $this->stats->categoryDonut($society, now()->startOfYear(), now()->endOfYear(), 'This Year'),
            'budgets' => $this->stats->budgetUsage($society, (int) now()->year),
            'year' => (int) now()->year,
        ]);
    }

    public function create(): View
    {
        return view('society.expenses.categories.create', [
            'icons' => self::ICONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $this->validateCategory($request);

        $category = ExpenseCategory::create([
            'society_id' => $society?->id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => $data['icon'] ?? 'fa-tag',
            'color' => self::ICONS[$data['icon'] ?? 'fa-tag'][1] ?? 'gray',
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'display_order' => $data['display_order'] ?? 0,
            'applicable_for' => $data['applicable_for'],
            'notes' => $data['notes'] ?? null,
        ]);
        $this->saveBudget($category, $data);

        return redirect()->route('society.expenses.categories.index')
            ->with('success', "Category \"{$category->name}\" created successfully.");
    }

    public function edit(ExpenseCategory $category): View
    {
        return view('society.expenses.categories.edit', [
            'category' => $category,
            'icons' => self::ICONS,
        ]);
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $data = $this->validateCategory($request);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => $data['icon'] ?? 'fa-tag',
            'color' => self::ICONS[$data['icon'] ?? 'fa-tag'][1] ?? 'gray',
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'display_order' => $data['display_order'] ?? 0,
            'applicable_for' => $data['applicable_for'],
            'notes' => $data['notes'] ?? null,
        ]);
        $this->saveBudget($category, $data);

        return redirect()->route('society.expenses.categories.index')
            ->with('success', "Category \"{$category->name}\" updated successfully.");
    }

    public function destroy(ExpenseCategory $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()->route('society.expenses.categories.index')
            ->with('success', "Category \"{$name}\" deleted.");
    }

    /**
     * Validate the Add/Edit category form.
     *
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::ICONS))],
            'description' => ['nullable', 'string', 'max:200'],
            'status' => ['required', 'in:active,inactive'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'applicable_for' => ['required', 'in:all_buildings,specific_buildings,specific_wings'],
            'notes' => ['nullable', 'string', 'max:200'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'budget_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);
    }

    /**
     * Upsert the yearly budget row for the category when an amount is given.
     *
     * @param  array<string, mixed>  $data
     */
    private function saveBudget(ExpenseCategory $category, array $data): void
    {
        if (! array_key_exists('budget_amount', $data) || $data['budget_amount'] === null || $data['budget_amount'] === '') {
            return;
        }

        ExpenseBudget::updateOrCreate(
            ['society_id' => $category->society_id, 'expense_category_id' => $category->id, 'year' => (int) ($data['budget_year'] ?? now()->year)],
            ['amount' => (float) $data['budget_amount']],
        );
    }
}
