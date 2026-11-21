<?php

namespace App\Http\Requests;

use App\Models\Visitor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitorRequest extends FormRequest
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
            'mobile' => ['nullable', 'string', 'max:30'],
            'purpose' => ['required', Rule::in(array_keys(Visitor::PURPOSES))],
            'company' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['required', Rule::exists('units', 'id')->where('society_id', $societyId)->whereNull('deleted_at')],
            'people_count' => ['required', 'integer', 'min:1', 'max:100'],
            'vehicle_no' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['unit_id' => 'unit'];
    }
}
