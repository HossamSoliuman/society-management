<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Tenant details; on create the first rental agreement can be captured too.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $societyId = $this->user()?->society_id;
        $creating = $this->route('tenant') === null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'unit_id' => ['required', Rule::exists('units', 'id')->where('society_id', $societyId)->whereNull('deleted_at')],
            'owner_member_id' => ['nullable', Rule::exists('members', 'id')->where('society_id', $societyId)->whereNull('deleted_at')],
            'occupants_count' => ['required', 'integer', 'min:1', 'max:50'],
            'id_proof_type' => ['nullable', Rule::in(Tenant::ID_PROOFS)],
            'id_proof_number' => ['nullable', 'string', 'max:100'],
            'permanent_address' => ['nullable', 'string', 'max:1000'],
            'move_in_date' => ['required', 'date'],
            'police_verification' => ['required', Rule::in(array_keys(Tenant::VERIFICATION))],
            'notes' => ['nullable', 'string', 'max:2000'],

            'agreement_start_date' => [$creating ? 'nullable' : 'prohibited', 'date', 'required_with:agreement_end_date'],
            'agreement_end_date' => [$creating ? 'nullable' : 'prohibited', 'date', 'after:agreement_start_date', 'required_with:agreement_start_date'],
            'agreement_number' => ['nullable', 'string', 'max:100'],
            'monthly_rent' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'security_deposit' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'agreement_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'unit_id' => 'unit',
            'owner_member_id' => 'owner',
            'agreement_start_date' => 'agreement start date',
            'agreement_end_date' => 'agreement end date',
        ];
    }
}
