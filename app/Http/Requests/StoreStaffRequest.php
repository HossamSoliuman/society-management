<?php

namespace App\Http\Requests;

use App\Models\Staff;
use App\Models\Tenant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(Staff::ROLES)],
            'employment_type' => ['required', Rule::in(array_keys(Staff::EMPLOYMENT_TYPES))],
            'vendor_id' => ['nullable', 'required_if:employment_type,agency', Rule::exists('service_vendors', 'id')->where('society_id', $societyId)],
            'shift' => ['required', Rule::in(array_keys(Staff::SHIFTS))],
            'mobile' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'id_proof_type' => ['nullable', Rule::in(Tenant::ID_PROOFS)],
            'id_proof_number' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'status' => ['required', Rule::in(array_keys(Staff::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['vendor_id' => 'agency'];
    }
}
