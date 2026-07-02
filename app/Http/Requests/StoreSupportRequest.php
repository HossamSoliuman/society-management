<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportRequest extends FormRequest
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
        return [
            'category' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:high,medium,low'],
            'subject' => ['required', 'string', 'max:255'],
            'raised_by_type' => ['required', 'in:member,staff_admin'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'raised_by_name' => ['nullable', 'string', 'max:255'],
            'flat_no' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'preferred_contact' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
