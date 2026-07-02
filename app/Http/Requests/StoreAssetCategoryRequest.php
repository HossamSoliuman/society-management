<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAssetCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'movable' => ['nullable', 'boolean'],
            'immovable' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'asset_life_years' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * At least one of movable/immovable must be selected.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('movable') && ! $this->boolean('immovable')) {
                $validator->errors()->add('movable', 'Select at least one applicable asset type (Movable or Immovable).');
            }
        });
    }
}
