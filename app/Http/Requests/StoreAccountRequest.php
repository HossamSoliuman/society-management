<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'group_id' => ['required', 'integer', Rule::exists('account_groups', 'id')->where('society_id', $this->user()->society_id)],
            'parent_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where('society_id', $this->user()->society_id)],
            'type' => ['required', 'in:group,detail'],
            'opening_balance' => ['nullable', 'numeric'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
