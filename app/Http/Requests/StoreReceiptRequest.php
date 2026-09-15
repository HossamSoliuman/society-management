<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceiptRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'payer_name' => ['required', 'string', 'max:255'],
            'flat_no' => ['nullable', 'string', 'max:50'],
            'receipt_type' => ['required', 'in:maintenance,other_charges,amenities,interest_penalty'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'mode_of_payment' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where('society_id', $this->user()->society_id)],
            'income_account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where('society_id', $this->user()->society_id)],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
