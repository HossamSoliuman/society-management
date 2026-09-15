<?php

namespace App\Http\Requests;

use App\Http\Controllers\Society\SupportController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(SupportController::CATEGORIES)],
            'priority' => ['required', Rule::in(array_keys(SupportController::PRIORITIES))],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'preferred_contact' => ['nullable', Rule::in(SupportController::CONTACT_METHODS)],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
