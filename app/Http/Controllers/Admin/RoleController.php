<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', ['roles' => Role::query()->withCount('users')->with('permissions')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.roles.create', ['permissionGroups' => $this->permissionGroups()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions', []));

        return to_route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(int $role): View
    {
        return view('admin.roles.edit', ['managedRole' => Role::with('permissions')->findOrFail($role), 'permissionGroups' => $this->permissionGroups()]);
    }

    public function update(UpdateRoleRequest $request, int $role): RedirectResponse
    {
        $managedRole = Role::findOrFail($role);
        if ($managedRole->name === 'super_admin' && $request->validated('name') !== 'super_admin') {
            throw ValidationException::withMessages(['name' => 'The super administrator role cannot be renamed.']);
        }
        $managedRole->update(['name' => $request->validated('name')]);
        $managedRole->syncPermissions($request->validated('permissions', []));

        return to_route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(int $role): RedirectResponse
    {
        $managedRole = Role::withCount('users')->findOrFail($role);
        if ($managedRole->name === 'super_admin') {
            return back()->with('error', 'The super administrator role cannot be deleted.');
        }
        if ($managedRole->users_count > 0) {
            return back()->with('error', 'Reassign users before deleting this role.');
        }
        $managedRole->delete();

        return to_route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    private function permissionGroups(): Collection
    {
        return Permission::query()->where('guard_name', 'web')->orderBy('name')->get()->groupBy(fn (Permission $permission) => str($permission->name)->before('.')->replace('_', ' ')->title()->value());
    }
}
