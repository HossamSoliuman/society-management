<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocietyUserRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Notifications\SocietyAdminInvitation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/**
 * Society-side "Roles & Permissions": manage the team that can sign in to
 * this society's panel and see which modules each role unlocks.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $users = User::query()
            ->with('roles')
            ->where('society_id', $society->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', StoreSocietyUserRequest::ASSIGNABLE_ROLES))
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $request->string('role'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $base = User::query()->where('society_id', $society->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', StoreSocietyUserRequest::ASSIGNABLE_ROLES));

        return view('society.settings.users.index', [
            'users' => $users,
            'roles' => $this->assignableRoles(),
            'permissionMatrix' => $this->permissionMatrix(),
            'stats' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 'active')->count(),
                'inactive' => (clone $base)->where('status', '!=', 'active')->count(),
                'admins' => (clone $base)->whereHas('roles', fn ($q) => $q->where('name', 'society_admin'))->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('society.settings.users.create', [
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(StoreSocietyUserRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();
        $role = $request->role();

        $user = User::create([
            'society_id' => $society->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'password' => Str::random(64),
            'status' => $data['status'],
        ]);
        $user->roles()->attach($role);

        $message = "User \"{$user->name}\" added.";
        if ($user->status === 'active') {
            $message .= $this->sendInvitation($user)
                ? ' A password setup invitation has been emailed.'
                : ' The invitation could not be sent — use "Resend invitation".';
        }

        return redirect()->route('society.settings.users.index')->with('success', $message);
    }

    public function edit(User $teamUser): View
    {
        $user = $teamUser;
        $this->guardTeamMember($user);

        return view('society.settings.users.edit', [
            'user' => $user->load('roles'),
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(StoreSocietyUserRequest $request, User $teamUser): RedirectResponse
    {
        $user = $teamUser;
        $this->guardTeamMember($user);
        $data = $request->validated();
        $role = $request->role();

        if ($user->is($request->user()) && ($role->name !== 'society_admin' || $data['status'] !== 'active')) {
            throw ValidationException::withMessages(['role_id' => 'You cannot remove your own active admin access.']);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'status' => $data['status'],
        ]);
        $user->roles()->sync([$role->id]);

        return redirect()->route('society.settings.users.index')->with('success', "User \"{$user->name}\" updated.");
    }

    public function toggleStatus(Request $request, User $teamUser): RedirectResponse
    {
        $user = $teamUser;
        $this->guardTeamMember($user);
        abort_if($user->is($request->user()), 422, 'You cannot deactivate your own account.');

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', "User \"{$user->name}\" is now {$user->status}.");
    }

    public function resendInvitation(User $teamUser): RedirectResponse
    {
        $user = $teamUser;
        $this->guardTeamMember($user);
        abort_unless($user->status === 'active', 422, 'Activate this user before sending an invitation.');

        if (! $this->sendInvitation($user)) {
            return back()->with('error', 'The invitation could not be sent. Check the mail settings and try again.');
        }

        return back()->with('success', "A fresh invitation was sent to {$user->email}.");
    }

    /**
     * The {teamUser} binding (AppServiceProvider) already restricts the row to
     * the current society; this additionally requires a society-panel role.
     */
    private function guardTeamMember(User $user): void
    {
        $society = $this->currentSociety();

        abort_unless(
            $user->society_id === $society->id && $user->hasAnyRole(StoreSocietyUserRequest::ASSIGNABLE_ROLES),
            404
        );
    }

    private function sendInvitation(User $user): bool
    {
        try {
            $token = Password::broker()->createToken($user);
            $user->notify(new SocietyAdminInvitation($token, $this->currentSociety()->name));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /**
     * @return Collection<int, Role>
     */
    private function assignableRoles(): Collection
    {
        return Role::query()
            ->where('status', 'active')
            ->whereIn('name', StoreSocietyUserRequest::ASSIGNABLE_ROLES)
            ->with('permissions')
            ->get()
            ->sortBy(fn (Role $role) => array_search($role->name, StoreSocietyUserRequest::ASSIGNABLE_ROLES, true))
            ->values();
    }

    /**
     * Module × role grid for the read-only permission matrix.
     *
     * @return array{permissions: Collection<int, Permission>, roles: Collection<int, Role>}
     */
    private function permissionMatrix(): array
    {
        $roles = $this->assignableRoles();
        $permissionNames = $roles->flatMap(fn (Role $role) => $role->permissions->pluck('name'))->unique();

        return [
            'permissions' => Permission::query()->whereIn('name', $permissionNames)->orderBy('module')->get(),
            'roles' => $roles,
        ];
    }
}
