<?php

namespace App\Http\Requests;

use App\Models\Notice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocietyNoticeRequest extends FormRequest
{
    /**
     * When editing, the notice must be one this society wrote; anything else
     * is reported as missing before validation runs.
     */
    public function authorize(): bool
    {
        $notice = $this->route('notice');

        if ($notice instanceof Notice) {
            abort_unless($notice->isSocietyAuthored() && (int) $notice->society_id === (int) $this->user()?->society_id, 404);
        }

        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'notice_type' => ['required', 'in:general,maintenance,billing,event'],
            'priority' => ['required', 'in:high,medium,low'],
            'audience' => ['required', Rule::in(array_keys(Notice::SOCIETY_AUDIENCES))],
            'short_description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:20000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'remove_attachment' => ['nullable', 'boolean'],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
            'pin_to_dashboard' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
            'send_sms' => ['nullable', 'boolean'],
            'require_acknowledgement' => ['nullable', 'boolean'],
            'action' => ['required', 'in:publish,draft'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'publish_at' => 'publish date',
            'expires_at' => 'expiry date',
        ];
    }
}
