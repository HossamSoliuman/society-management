<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Society;
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

    public function index(Request $request): View
    {
        $society = Society::first();

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
                'top' => 'Maintenance',
            ],
            'usage' => $this->usageDonut(),
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
        $society = Society::first();
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
        ]);
    }

    /**
     * Category Usage donut + legend (rail card in "expense category list.png").
     *
     * @return array<string, mixed>
     */
    private function usageDonut(): array
    {
        return [
            'center_value' => '&#8377; 82,450',
            'center_label' => 'Total Expense',
            'segments' => [
                ['label' => 'Maintenance', 'amount' => '&#8377; 28,450', 'pct' => '34.5%', 'value' => 28450, 'color' => '#F97316'],
                ['label' => 'Utilities', 'amount' => '&#8377; 18,750', 'pct' => '22.7%', 'value' => 18750, 'color' => '#3B82F6'],
                ['label' => 'Salary', 'amount' => '&#8377; 16,200', 'pct' => '19.6%', 'value' => 16200, 'color' => '#10B981'],
                ['label' => 'Security', 'amount' => '&#8377; 9,800', 'pct' => '11.9%', 'value' => 9800, 'color' => '#8B5CF6'],
                ['label' => 'Others', 'amount' => '&#8377; 9,250', 'pct' => '11.2%', 'value' => 9250, 'color' => '#94a3b8'],
            ],
        ];
    }
}
