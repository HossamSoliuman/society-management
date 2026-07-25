<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAmcContractRequest extends FormRequest
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
            'item_asset' => ['required', 'string', 'max:255'],
            'item_sub' => ['nullable', 'string', 'max:255'],
            'amc_category_id' => ['required', 'integer', Rule::exists('amc_categories', 'id')->where('society_id', $this->user()->society_id)],
            'service_vendor_id' => ['nullable', 'integer', Rule::exists('service_vendors', 'id')->where('society_id', $this->user()->society_id)],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'contract_no' => ['nullable', 'string', 'max:255'],
            'po_invoice_no' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['required', 'in:Comprehensive,Non-Comprehensive'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'duration_months' => ['nullable', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'renewal_reminder_days' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'in:draft,save'],
        ];
    }
}
