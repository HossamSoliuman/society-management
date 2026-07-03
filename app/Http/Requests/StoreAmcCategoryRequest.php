<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAmcCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:250'],
            'icon' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'applicable_assets' => ['nullable', 'array'],
            'applicable_assets.*' => ['string', 'max:255'],
            'default_reminder_days' => ['nullable', 'integer', 'min:0'],
            'default_duration_months' => ['nullable', 'integer', 'min:1'],
            'tax_applicable' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:250'],
        ];
    }
}
