<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountingPaymentRequest extends FormRequest
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
            'payee' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'mode' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where('society_id', $this->user()->society_id)],
            'expense_account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where('society_id', $this->user()->society_id)],
            'reference_no' => ['nullable', 'string', 'max:255'],
        ];
    }
}
