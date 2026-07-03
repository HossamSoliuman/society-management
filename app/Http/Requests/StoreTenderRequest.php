<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTenderRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'tender_type' => ['required', 'in:service,supply'],
            'department' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'tender_category' => ['required', 'string', 'max:100'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'emd_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'submission_deadline' => ['nullable', 'date'],
            'opening_date' => ['nullable', 'date'],
            'validity_days' => ['nullable', 'integer', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'delivery_terms' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['nullable', 'string', 'max:100'],
            'tax_option' => ['nullable', 'string', 'max:100'],
            'terms_conditions' => ['nullable', 'string'],
            'eligibility_criteria' => ['nullable', 'string', 'max:1000'],
            'evaluation_criteria' => ['nullable', 'string', 'max:1000'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'venue' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'string', 'max:50'],
            'allow_online_submission' => ['nullable', 'boolean'],
            'allow_partial_bidding' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
