<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255'], 'role' => ['nullable', 'string', 'exists:roles,name']]);

        return view('admin.users.index', [
            'users' => User::query()->with('roles')
                ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->when($filters['role'] ?? null, fn (Builder $query, string $role) => $query->role($role))
                ->orderBy('name')->paginate(15)->withQueryString(),
            'roles' => $this->roles(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => $this->roles()]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $user = User::create([
                ...$request->safe()->only(['name', 'email', 'password']),
                'email_verified_at' => now(),
            ]);
            $user->syncRoles($request->validated('roles'));
        });

        return to_route('admin.users.index')->with('success', 'Administrator created successfully.');
    }

    public function edit(int $user): View
    {
        return view('admin.users.edit', ['managedUser' => User::with('roles')->findOrFail($user), 'roles' => $this->roles()]);
    }

    public function update(UpdateAdminUserRequest $request, int $user): RedirectResponse
    {
        $managedUser = User::findOrFail($user);
        $roles = $request->validated('roles');
        $this->guardLastSuperAdmin($managedUser, $roles);

        DB::transaction(function () use ($request, $managedUser, $roles): void {
            $data = $request->safe()->only(['name', 'email']);
            if ($request->filled('password')) {
                $data['password'] = $request->validated('password');
            }
            $managedUser->update($data);
            $managedUser->syncRoles($roles);
        });

        return to_route('admin.users.index')->with('success', 'Administrator updated successfully.');
    }

    public function destroy(Request $request, int $user): RedirectResponse
    {
        $managedUser = User::findOrFail($user);
        if ($request->user()->is($managedUser)) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($this->isLastSuperAdmin($managedUser, [])) {
            return back()->with('error', 'The last super administrator cannot be deleted.');
        }
        $managedUser->delete();

        return to_route('admin.users.index')->with('success', 'Administrator deleted successfully.');
    }

    private function guardLastSuperAdmin(User $user, array $newRoles): void
    {
        if ($this->isLastSuperAdmin($user, $newRoles)) {
            throw ValidationException::withMessages(['roles' => 'The last super administrator must retain the super admin role.']);
        }
    }

    private function isLastSuperAdmin(User $user, array $newRoles): bool
    {
        return $user->hasRole('super_admin') && ! in_array('super_admin', $newRoles, true) && User::role('super_admin')->count() <= 1;
    }

    private function roles()
    {
        return Role::query()->where('guard_name', 'web')->withCount('permissions')->orderBy('name')->get();
    }
}
