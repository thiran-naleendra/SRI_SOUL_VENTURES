<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_list_search_and_filter_administrators(): void
    {
        $admin = $this->superAdmin();
        User::factory()->create(['name' => 'Content Needle', 'email' => 'content@example.test'])->assignRole('content_manager');
        User::factory()->create(['name' => 'Consultant Miss', 'email' => 'consultant@example.test'])->assignRole('tour_consultant');

        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Needle', 'role' => 'content_manager']))
            ->assertOk()->assertSee('Content Needle')->assertDontSee('Consultant Miss')->assertSee('Add administrator');
    }

    public function test_super_admin_can_create_and_update_an_administrator_with_roles(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Manager', 'email' => 'manager@example.test', 'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123', 'roles' => ['content_manager'],
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'manager@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('StrongPassword123', $user->password));
        $this->assertTrue($user->hasRole('content_manager'));

        $this->put(route('admin.users.update', $user), [
            'name' => 'Updated Manager', 'email' => 'manager@example.test', 'password' => '',
            'password_confirmation' => '', 'roles' => ['tour_consultant'],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue($user->fresh()->hasRole('tour_consultant'));
        $this->assertSame('Updated Manager', $user->fresh()->name);
    }

    public function test_user_validation_and_last_super_admin_safeguards_are_enforced(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => '', 'email' => 'invalid', 'password' => 'short', 'password_confirmation' => 'different', 'roles' => [],
        ])->assertSessionHasErrors(['name', 'email', 'password', 'roles']);

        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'roles' => ['administrator'],
        ])->assertSessionHasErrors('roles');
        $this->delete(route('admin.users.destroy', $admin))->assertRedirect()->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->hasRole('super_admin'));
    }

    public function test_roles_can_be_created_updated_and_deleted_with_permissions(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'seo_manager', 'permissions' => ['pages.manage', 'settings.manage'],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('seo_manager');
        $this->assertTrue($role->hasPermissionTo('pages.manage'));

        $this->put(route('admin.roles.update', $role), [
            'name' => 'seo_editor', 'permissions' => ['pages.manage'],
        ])->assertRedirect(route('admin.roles.index'));

        $role->refresh();
        $this->assertSame('seo_editor', $role->name);
        $this->assertSame(['pages.manage'], $role->permissions->pluck('name')->all());

        $this->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_super_admin_role_and_assigned_roles_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $superRole = Role::findByName('super_admin');
        $assignedRole = Role::create(['name' => 'assigned_role', 'guard_name' => 'web']);
        User::factory()->create()->assignRole($assignedRole);

        $this->actingAs($admin)->delete(route('admin.roles.destroy', $superRole))->assertRedirect()->assertSessionHas('error');
        $this->delete(route('admin.roles.destroy', $assignedRole))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $superRole->id]);
        $this->assertDatabaseHas('roles', ['id' => $assignedRole->id]);
    }

    public function test_users_without_management_permissions_are_forbidden_from_all_write_routes(): void
    {
        $user = User::factory()->create();
        $role = Role::findByName('content_manager');

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->post(route('admin.users.store'))->assertForbidden();
        $this->get(route('admin.roles.index'))->assertForbidden();
        $this->put(route('admin.roles.update', $role))->assertForbidden();
    }

    public function test_role_form_displays_every_permission_group(): void
    {
        $this->actingAs($this->superAdmin())->get(route('admin.roles.create'))
            ->assertOk()->assertSee('Permissions')->assertSee('Admin')->assertSee('Destinations')
            ->assertSee(Permission::firstOrFail()->name)->assertSee('Check all');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
