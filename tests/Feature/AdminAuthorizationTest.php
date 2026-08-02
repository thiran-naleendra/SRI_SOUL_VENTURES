<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_from_admin_routes(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    public function test_authenticated_user_without_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_role_cannot_access_an_unassigned_admin_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tour_consultant');

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertForbidden();
    }

    public function test_role_can_access_an_assigned_admin_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tour_consultant');

        $this->actingAs($user)
            ->get('/admin/package-enquiries')
            ->assertOk();
    }

    public function test_super_admin_has_every_configured_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertCount(24, $user->getAllPermissions());
        $this->actingAs($user)->get('/admin/settings')->assertOk();
    }
}
