<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assetId = $this->route('asset')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'asset_code' => ['required', 'string', 'max:255', Rule::unique('assets', 'asset_code')->ignore($assetId)],
            'category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['required', 'string', 'max:255'],
            'tower_wing' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:255'],
            'area_room' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'vendor_supplier' => ['nullable', 'string', 'max:255'],
            'purchase_from' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['required', 'date'],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'warranty_start' => ['nullable', 'date'],
            'warranty_end' => ['nullable', 'date'],
            'invoice_no' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'expected_life_years' => ['nullable', 'integer', 'min:0'],
            'depreciation_method' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:in_use,under_maintenance,inactive,disposed'],
            'condition' => ['required', 'in:good,fair,poor'],
            'usage_type' => ['nullable', 'string', 'max:255'],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
