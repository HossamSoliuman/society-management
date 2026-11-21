<?php

namespace App\Http\Requests;

use App\Models\Visitor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared by the gate (society panel, picks a unit) and the resident portal
 * (unit is the resident's own, so unit_id is not asked for).
 */
class StoreVisitorPreApprovalRequest extends FormRequest
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
        $fromPortal = $this->routeIs('member.*');

        return [
            'visitor_name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'purpose' => ['required', Rule::in(array_keys(Visitor::PURPOSES))],
            'people_count' => ['required', 'integer', 'min:1', 'max:100'],
            'vehicle_no' => ['nullable', 'string', 'max:30'],
            'expected_at' => ['required', 'date', 'after_or_equal:'.now()->subHour()->format('Y-m-d H:i')],
            'unit_id' => [$fromPortal ? 'prohibited' : 'required', Rule::exists('units', 'id')->where('society_id', $societyId)->whereNull('deleted_at')],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['unit_id' => 'unit', 'expected_at' => 'expected arrival'];
    }
}
