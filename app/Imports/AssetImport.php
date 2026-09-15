<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Society;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

/**
 * Imports assets from a spreadsheet. Headings: Name, Category, Brand, Model,
 * Serial Number, Location, Purchase Date, Purchase Cost, Warranty End, Status,
 * Condition, Notes. Unknown categories are created on the fly.
 */
class AssetImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    /** @var array<int, array{row: int, error: string}> */
    public array $errors = [];

    public function __construct(private readonly Society $society) {}

    public function collection(Collection $rows): void
    {
        $categories = AssetCategory::query()->forSociety($this->society)->get()->keyBy(fn ($c) => strtolower($c->name));

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $row = collect($row)->mapWithKeys(fn ($v, $k) => [strtolower(trim((string) $k)) => $v]);
            $name = trim((string) ($row['name'] ?? $row['asset_name'] ?? ''));
            if ($name === '') {
                if ($row->filter()->isEmpty()) {
                    continue;
                }
                $this->errors[] = ['row' => $line, 'error' => 'Name is required.'];

                continue;
            }

            $categoryName = trim((string) ($row['category'] ?? ''));
            $category = $categoryName !== '' ? $categories->get(strtolower($categoryName)) : null;
            if (! $category && $categoryName !== '') {
                $category = AssetCategory::create([
                    'society_id' => $this->society->id,
                    'name' => $categoryName,
                    'code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $categoryName), 0, 3)) ?: 'CAT',
                    'status' => 'active',
                ]);
                $categories->put(strtolower($categoryName), $category);
            }
            if (! $category) {
                $this->errors[] = ['row' => $line, 'error' => 'Category is required.'];

                continue;
            }

            $status = strtolower(str_replace(' ', '_', trim((string) ($row['status'] ?? 'in_use'))));
            $condition = strtolower(trim((string) ($row['condition'] ?? 'good')));

            Asset::create([
                'society_id' => $this->society->id,
                'name' => $name,
                'asset_code' => $this->nextCode(),
                'category_id' => $category->id,
                'brand' => trim((string) ($row['brand'] ?? '')) ?: null,
                'model' => trim((string) ($row['model'] ?? '')) ?: null,
                'serial_number' => trim((string) ($row['serial_number'] ?? $row['serial'] ?? '')) ?: null,
                'location' => trim((string) ($row['location'] ?? '')) ?: null,
                'tower_wing' => trim((string) ($row['tower'] ?? $row['tower_wing'] ?? '')) ?: null,
                'purchase_date' => $this->date($row['purchase_date'] ?? null),
                'purchase_cost' => is_numeric($row['purchase_cost'] ?? null) ? (float) $row['purchase_cost'] : 0,
                'warranty_end' => $this->date($row['warranty_end'] ?? null),
                'status' => in_array($status, ['in_use', 'under_maintenance', 'inactive', 'disposed'], true) ? $status : 'in_use',
                'condition' => in_array($condition, ['good', 'fair', 'poor'], true) ? $condition : 'good',
                'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
                'current_value' => is_numeric($row['current_value'] ?? null) ? (float) $row['current_value'] : (is_numeric($row['purchase_cost'] ?? null) ? (float) $row['purchase_cost'] : null),
            ]);
            $this->created++;
        }
    }

    private function nextCode(): string
    {
        $next = (int) Asset::query()->max('id') + 1;
        do {
            $code = 'AST'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Asset::query()->where('asset_code', $code)->exists());

        return $code;
    }

    private function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            if (is_numeric($value)) {
                return Carbon::createFromDate(1899, 12, 30)->addDays((int) $value)->toDateString();
            }
            $value = trim((string) $value);
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
                return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->toDateString();
            }

            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
