<?php

namespace Tests\Feature\Admin;

use App\Models\ContactEnquiry;
use App\Models\CustomTourRequest;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Models\PackageEnquiry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_dashboard_uses_real_aggregate_data_and_limited_lists(): void
    {
        $activeDestination = Destination::factory()->create(['is_active' => true]);
        Destination::factory()->create(['is_active' => false]);
        Experience::factory()->for($activeDestination)->create(['is_active' => true, 'is_popular' => true, 'title' => 'Popular Active Experience']);
        Experience::factory()->for($activeDestination)->create(['is_active' => false, 'is_popular' => true, 'title' => 'Popular Inactive Experience']);
        $activePackage = Package::factory()->create(['is_active' => true, 'is_popular' => true, 'title' => 'Popular Active Package']);
        Package::factory()->create(['is_active' => false, 'is_popular' => true, 'title' => 'Popular Inactive Package']);

        PackageEnquiry::factory()->for($activePackage)->count(2)->create(['status' => 'new']);
        PackageEnquiry::factory()->for($activePackage)->create(['status' => 'contacted']);
        CustomTourRequest::factory()->count(2)->create(['status' => 'new']);
        CustomTourRequest::factory()->create(['status' => 'planning']);
        ContactEnquiry::create(['name' => 'Unread Contact', 'email' => 'unread@example.test', 'message' => 'Unread', 'is_read' => false]);
        ContactEnquiry::create(['name' => 'Read Contact', 'email' => 'read@example.test', 'message' => 'Read', 'is_read' => true]);

        $response = $this->actingAs($this->userWithRole('super_admin'))->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertViewHas('destinationStats', fn ($stats) => (int) $stats->total === 2 && (int) $stats->active === 1)
            ->assertViewHas('experienceStats', fn ($stats) => (int) $stats->total === 2 && (int) $stats->active === 1)
            ->assertViewHas('packageStats', fn ($stats) => (int) $stats->total === 2 && (int) $stats->active === 1)
            ->assertViewHas('packageEnquiryStats', fn ($stats) => (int) $stats->total === 3 && (int) $stats->new_count === 2)
            ->assertViewHas('customTourStats', fn ($stats) => (int) $stats->total === 3 && (int) $stats->new_count === 2)
            ->assertViewHas('contactEnquiryStats', fn ($stats) => (int) $stats->total === 2 && (int) $stats->unread === 1)
            ->assertSee('Popular Active Package')
            ->assertDontSee('Popular Inactive Package')
            ->assertSee('Popular Active Experience')
            ->assertDontSee('Popular Inactive Experience')
            ->assertSee('Unread Contact')
            ->assertSee('dashboard-chart', false)
            ->assertSee('Monthly enquiries');
    }

    public function test_recent_widgets_show_only_five_latest_records(): void
    {
        $package = Package::factory()->create();
        PackageEnquiry::factory()->for($package)->create(['customer_name' => 'Oldest Hidden Enquiry', 'created_at' => now()->subDays(10)]);
        foreach (range(1, 5) as $number) {
            PackageEnquiry::factory()->for($package)->create(['customer_name' => "Recent Enquiry {$number}", 'created_at' => now()->subDays(5 - $number)]);
        }

        $this->actingAs($this->userWithRole('super_admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Oldest Hidden Enquiry')
            ->assertSee('Recent Enquiry 1')
            ->assertSee('Recent Enquiry 5')
            ->assertViewHas('recentPackageEnquiries', fn ($items) => $items->count() === 5);
    }

    public function test_dashboard_handles_popular_content_with_soft_deleted_relationships(): void
    {
        $destination = Destination::factory()->create();
        $experience = Experience::factory()->for($destination)->create([
            'is_popular' => true,
            'title' => 'Legacy Experience',
        ]);
        $package = Package::factory()->create([
            'is_popular' => true,
            'title' => 'Legacy Package',
        ]);

        $experience->category->delete();
        $destination->delete();
        $package->category->delete();

        $this->actingAs($this->userWithRole('super_admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Legacy Experience')
            ->assertSee('Unavailable destination')
            ->assertSee('Uncategorized')
            ->assertSee('Legacy Package');
    }

    public function test_monthly_chart_combines_authorized_enquiry_sources_for_last_twelve_months(): void
    {
        PackageEnquiry::factory()->create(['created_at' => now()->startOfMonth()]);
        CustomTourRequest::factory()->create(['created_at' => now()->startOfMonth()]);
        ContactEnquiry::create(['name' => 'Monthly Contact', 'email' => 'month@example.test', 'message' => 'Month', 'created_at' => now()->startOfMonth()]);
        PackageEnquiry::factory()->create(['created_at' => now()->subMonths(13)]);

        $this->actingAs($this->userWithRole('super_admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('monthlyEnquiries', function ($months) {
                return $months->count() === 12
                    && $months->last()['key'] === now()->format('Y-m')
                    && $months->last()['total'] === 3;
            });
    }

    public function test_content_manager_only_receives_content_widgets(): void
    {
        Destination::factory()->create();
        PackageEnquiry::factory()->create(['customer_name' => 'Restricted Enquiry']);

        $this->actingAs($this->userWithRole('content_manager'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('destinationStats')
            ->assertViewMissing('packageEnquiryStats')
            ->assertViewMissing('customTourStats')
            ->assertViewMissing('contactEnquiryStats')
            ->assertSee('Total destinations')
            ->assertDontSee('Total package enquiries')
            ->assertDontSee('Restricted Enquiry')
            ->assertDontSee('Monthly enquiries');
    }

    public function test_tour_consultant_only_receives_authorized_enquiry_widgets(): void
    {
        Destination::factory()->create(['name' => 'Restricted Destination']);
        PackageEnquiry::factory()->create(['customer_name' => 'Visible Package Lead']);
        CustomTourRequest::factory()->create(['customer_name' => 'Visible Custom Lead']);
        ContactEnquiry::create(['name' => 'Visible Contact Lead', 'email' => 'visible@example.test', 'message' => 'Hello']);

        $this->actingAs($this->userWithRole('tour_consultant'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewMissing('destinationStats')
            ->assertViewMissing('experienceStats')
            ->assertViewMissing('packageStats')
            ->assertViewHas('packageEnquiryStats')
            ->assertViewHas('customTourStats')
            ->assertViewHas('contactEnquiryStats')
            ->assertDontSee('Restricted Destination')
            ->assertSee('Visible Package Lead')
            ->assertSee('Visible Custom Lead')
            ->assertSee('Visible Contact Lead')
            ->assertSee('Monthly enquiries');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
