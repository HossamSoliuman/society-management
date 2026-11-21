<?php

namespace App\Http\Requests;

use App\Http\Controllers\Society\SupportController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
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
        $societyId = $this->user()?->society_id;

        return [
            'member_id' => ['required', Rule::exists('members', 'id')->where('society_id', $societyId)->whereNull('deleted_at')],
            'category' => ['required', Rule::in(SupportController::CATEGORIES)],
            'priority' => ['required', Rule::in(array_keys(SupportController::PRIORITIES))],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('society_id', $societyId)],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['member_id' => 'resident', 'assigned_to' => 'assignee'];
    }
}
