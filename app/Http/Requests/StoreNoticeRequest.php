<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'notice_type' => ['required', 'in:general,maintenance,billing,event'],
            'priority' => ['required', 'in:high,medium,low'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'attach_path' => ['nullable', 'string', 'max:255'],
            'publish_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
            'pin_to_dashboard' => ['nullable', 'boolean'],
            'audience_type' => ['required', 'in:all_members,selected_members,selected_units,selected_towers,custom'],
            'estimated_recipients' => ['nullable', 'integer', 'min:0'],
            'send_email' => ['nullable', 'boolean'],
            'send_sms' => ['nullable', 'boolean'],
            'require_acknowledgement' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:published,scheduled,draft,expired'],
        ];
    }
}
