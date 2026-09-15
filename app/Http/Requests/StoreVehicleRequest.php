<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('registration_no')) {
            $this->merge(['registration_no' => strtoupper(trim((string) $this->input('registration_no')))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $societyId = $this->user()?->society_id;
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'registration_no' => [
                'required', 'string', 'max:30',
                Rule::unique('vehicles', 'registration_no')->where('society_id', $societyId)->ignore($vehicleId),
            ],
            'vehicle_type' => ['required', Rule::in(array_keys(Vehicle::TYPES))],
            'make' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'rfid_tag' => ['nullable', 'string', 'max:100'],
            'unit_id' => ['nullable', 'integer', Rule::exists('units', 'id')->where('society_id', $societyId)],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_no.unique' => 'This registration number is already recorded in your society.',
        ];
    }
}
