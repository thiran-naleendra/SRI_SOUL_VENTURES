<?php

namespace Tests\Feature\Admin;

use App\Models\DestinationRegion;
use App\Models\ExperienceCategory;
use App\Models\PackageCategory;
use App\Models\TravelStyle;
use App\Models\User;
use Database\Seeders\CatalogTaxonomySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaxonomyCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public static function modules(): array
    {
        return [
            'destination regions' => [DestinationRegion::class, 'admin.destination-regions', 'short_description'],
            'experience categories' => [ExperienceCategory::class, 'admin.experience-categories', 'description'],
            'travel styles' => [TravelStyle::class, 'admin.travel-styles', 'description'],
            'package categories' => [PackageCategory::class, 'admin.package-categories', 'description'],
        ];
    }

    #[DataProvider('modules')]
    public function test_super_admin_can_complete_the_crud_lifecycle(string $modelClass, string $routePrefix, string $descriptionField): void
    {
        $this->actingAs($this->superAdmin());

        $payload = [
            'name' => 'Island Discovery',
            $descriptionField => 'A useful description.',
            'icon' => 'compass',
            'display_order' => 20,
            'is_active' => 1,
        ];

        $this->post(route("{$routePrefix}.store"), $payload)
            ->assertRedirect(route("{$routePrefix}.index"))
            ->assertSessionHas('success');

        $item = $modelClass::query()->where('name', 'Island Discovery')->firstOrFail();
        $this->assertSame('island-discovery', $item->slug);
        $this->assertTrue($item->is_active);

        $this->post(route("{$routePrefix}.store"), $payload)->assertRedirect();
        $this->assertDatabaseHas($item->getTable(), ['slug' => 'island-discovery-2']);

        $this->get(route("{$routePrefix}.edit", $item))->assertOk()->assertSee('Island Discovery');
        $this->put(route("{$routePrefix}.update", $item), array_merge($payload, ['name' => 'Island Explorer', 'display_order' => 5]))
            ->assertRedirect(route("{$routePrefix}.index"));

        $item->refresh();
        $this->assertSame('island-explorer', $item->slug);
        $this->assertSame(5, $item->display_order);

        $this->patch(route("{$routePrefix}.toggle", $item))->assertRedirect()->assertSessionHas('success');
        $this->assertFalse($item->fresh()->is_active);

        $this->delete(route("{$routePrefix}.destroy", $item))->assertRedirect()->assertSessionHas('success');
        $this->assertSoftDeleted($item->getTable(), ['id' => $item->id]);
        $this->get(route("{$routePrefix}.index", ['status' => 'trashed']))->assertOk()->assertSee('Island Explorer');

        $this->patch(route("{$routePrefix}.restore", $item))->assertRedirect()->assertSessionHas('success');
        $this->assertNotSoftDeleted($item->getTable(), ['id' => $item->id]);
    }

    #[DataProvider('modules')]
    public function test_index_supports_search_status_filtering_and_pagination(string $modelClass, string $routePrefix, string $descriptionField): void
    {
        $this->actingAs($this->superAdmin());

        foreach (range(1, 17) as $number) {
            $modelClass::create([
                'name' => "Entry {$number}",
                'slug' => "entry-{$number}",
                $descriptionField => null,
                'display_order' => $number,
                'is_active' => $number !== 17,
            ]);
        }

        $this->get(route("{$routePrefix}.index"))->assertOk()->assertSee('page=2', false);
        $this->get(route("{$routePrefix}.index", ['search' => 'Entry 17']))->assertOk()->assertSee('Entry 17')->assertDontSee('Entry 16');
        $this->get(route("{$routePrefix}.index", ['status' => 'inactive']))->assertOk()->assertSee('Entry 17')->assertDontSee('Entry 16');
    }

    #[DataProvider('modules')]
    public function test_module_rejects_guests_and_users_without_permissions(string $modelClass, string $routePrefix, string $descriptionField): void
    {
        $this->get(route("{$routePrefix}.index"))->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->post(route("{$routePrefix}.store"), ['name' => 'Blocked', $descriptionField => null, 'display_order' => 0, 'is_active' => 1])
            ->assertForbidden();
    }

    public function test_validation_errors_are_returned(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.destination-regions.store'), ['name' => '', 'display_order' => -1])
            ->assertSessionHasErrors(['name', 'display_order']);
    }

    public function test_index_includes_delete_confirmation_markup(): void
    {
        $this->actingAs($this->superAdmin());
        DestinationRegion::factory()->create();

        $this->get(route('admin.destination-regions.index'))
            ->assertOk()
            ->assertSee('data-bs-target="#confirmationModal"', false)
            ->assertSee('data-confirm-action=', false);
    }

    public function test_catalog_taxonomy_seeder_is_idempotent_and_seeds_all_requested_values(): void
    {
        $this->seed(CatalogTaxonomySeeder::class);
        $this->seed(CatalogTaxonomySeeder::class);

        $this->assertSame(8, DestinationRegion::count());
        $this->assertSame(8, ExperienceCategory::count());
        $this->assertSame(9, TravelStyle::count());
        $this->assertSame(7, PackageCategory::count());
        $this->assertDatabaseHas('destination_regions', ['name' => 'Colombo and Around', 'slug' => Str::slug('Colombo and Around')]);
        $this->assertDatabaseHas('experience_categories', ['name' => 'Food and Local Life']);
        $this->assertDatabaseHas('travel_styles', ['name' => 'Honeymoon']);
        $this->assertDatabaseHas('package_categories', ['name' => 'Complete Sri Lanka']);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
