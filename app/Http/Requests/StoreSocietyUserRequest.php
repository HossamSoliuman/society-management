<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocietyUserRequest extends FormRequest
{
    /**
     * Roles a society admin may assign to team members.
     *
     * @var array<int, string>
     */
    public const ASSIGNABLE_ROLES = ['society_admin', 'manager', 'accountant', 'staff'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->route('teamUser')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'mobile' => ['nullable', 'string', 'max:20'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where('status', 'active')->whereIn('name', self::ASSIGNABLE_ROLES),
            ],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function role(): Role
    {
        return Role::query()->findOrFail($this->integer('role_id'));
    }
}
