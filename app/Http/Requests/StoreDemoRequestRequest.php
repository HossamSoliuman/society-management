<?php

namespace App\Http\Requests;

use App\Models\DemoRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemoRequestRequest extends FormRequest
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
            'society_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'units' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'role' => ['required', Rule::in(array_keys(DemoRequest::ROLES))],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'society_name' => 'society name',
            'units' => 'number of flats',
            'role' => 'your role',
            'phone' => 'phone number',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number using digits, spaces, + or -.',
        ];
    }
}
