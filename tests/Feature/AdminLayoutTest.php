<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    private array $modules = [
        'admin.dashboard' => 'admin.dashboard.view',
        'admin.destinations.index' => 'destinations.view',
        'admin.destination-regions.index' => 'destinations.view',
        'admin.experiences.index' => 'experiences.view',
        'admin.experience-categories.index' => 'experiences.view',
        'admin.packages.index' => 'packages.view',
        'admin.package-categories.index' => 'packages.view',
        'admin.package-enquiries.index' => 'enquiries.view',
        'admin.custom-tour-requests.index' => 'custom_tours.view',
        'admin.contact-enquiries.index' => 'enquiries.view',
        'admin.testimonials.index' => 'testimonials.manage',
        'admin.team-members.index' => 'team.manage',
        'admin.faqs.index' => 'faqs.manage',
        'admin.pages.index' => 'pages.manage',
        'admin.settings.index' => 'settings.manage',
        'admin.users.index' => 'users.manage',
        'admin.roles.index' => 'roles.manage',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_every_admin_module_has_the_required_route_protection(): void
    {
        foreach ($this->modules as $name => $permission) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route [{$name}].");
            $this->assertStringStartsWith('admin', $route->uri());
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertContains("permission:{$permission}", $route->gatherMiddleware());
        }
    }

    public function test_guests_cannot_access_any_admin_module(): void
    {
        foreach (array_keys($this->modules) as $name) {
            $this->get(route($name))->assertRedirect('/login');
        }
    }

    public function test_admin_layout_renders_navigation_and_responsive_controls(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->withSession(['success' => 'Saved successfully.', 'error' => 'Example error.'])
            ->get(route('admin.destinations.index'))
            ->assertOk()
            ->assertSee('Sri Soul')
            ->assertSee('Destination Regions')
            ->assertSee('Website Settings')
            ->assertSee('data-bs-target="#adminSidebar"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('Saved successfully.')
            ->assertSee('Example error.')
            ->assertSee('confirmationModal')
            ->assertSee('Logout');
    }

    public function test_navigation_hides_modules_the_user_cannot_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tour_consultant');

        $this->actingAs($user)
            ->get(route('admin.package-enquiries.index'))
            ->assertOk()
            ->assertSee('Package Enquiries')
            ->assertSee('Custom Tour Requests')
            ->assertDontSee('Website Settings')
            ->assertDontSee('Users');
    }
}
