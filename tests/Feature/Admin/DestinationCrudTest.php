<?php

namespace Tests\Feature\Admin;

use App\Models\Destination;
use App\Models\DestinationAttraction;
use App\Models\DestinationImage;
use App\Models\DestinationRegion;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DestinationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_destination_with_all_child_content_and_images(): void
    {
        $region = DestinationRegion::factory()->create();
        $this->actingAs($this->superAdmin());

        $response = $this->post(route('admin.destinations.store'), $this->payload($region, [
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
            'gallery' => [
                ['image' => UploadedFile::fake()->image('one.jpg'), 'alt_text' => 'First', 'caption' => 'One', 'display_order' => 20],
                ['image' => UploadedFile::fake()->image('two.webp'), 'alt_text' => 'Second', 'caption' => 'Two', 'display_order' => 10],
            ],
            'attractions' => [['title' => 'Temple', 'description' => 'Historic temple', 'image' => UploadedFile::fake()->image('temple.png'), 'display_order' => 1]],
            'activities' => [['title' => 'Hiking', 'description' => 'Hill walk', 'icon' => 'hiking', 'display_order' => 1]],
            'travel_tips' => [['title' => 'Pack light', 'description' => 'Bring water', 'display_order' => 1]],
        ]));

        $response->assertRedirect(route('admin.destinations.index'))->assertSessionHas('success');
        $destination = Destination::query()->where('slug', 'ella-highlands')->firstOrFail();
        $this->assertSame('Ella Highlands', $destination->name);
        $this->assertCount(2, $destination->images);
        $this->assertSame('Second', $destination->images->first()->alt_text);
        $this->assertCount(1, $destination->attractions);
        $this->assertCount(1, $destination->activities);
        $this->assertCount(1, $destination->travelTips);
        Storage::disk('public')->assertExists($destination->cover_image);
        $destination->images->each(fn (DestinationImage $image) => Storage::disk('public')->assertExists($image->image_path));
        Storage::disk('public')->assertExists($destination->attractions->first()->image_path);
    }

    public function test_update_safely_replaces_cover_and_attraction_image_and_removes_gallery_image(): void
    {
        $destination = Destination::factory()->create(['cover_image' => 'destinations/covers/old.jpg']);
        $gallery = DestinationImage::create(['destination_id' => $destination->id, 'image_path' => 'destinations/gallery/old-gallery.jpg']);
        $attraction = DestinationAttraction::create(['destination_id' => $destination->id, 'title' => 'Old title', 'image_path' => 'destinations/attractions/old.jpg']);
        foreach ([$destination->cover_image, $gallery->image_path, $attraction->image_path] as $path) {
            Storage::disk('public')->put($path, 'old');
        }
        $this->actingAs($this->superAdmin());

        $this->put(route('admin.destinations.update', $destination), $this->payload($destination->region, [
            'name' => 'Updated Ella',
            'slug' => '',
            'cover_image' => UploadedFile::fake()->image('new-cover.jpg'),
            'gallery' => [['id' => $gallery->id, '_remove' => 1, 'display_order' => 0]],
            'attractions' => [['id' => $attraction->id, 'title' => 'New title', 'image' => UploadedFile::fake()->image('new-attraction.jpg'), 'display_order' => 0]],
        ]))->assertRedirect(route('admin.destinations.edit', $destination));

        $destination->refresh();
        $attraction->refresh();
        $this->assertSame('updated-ella', $destination->slug);
        $this->assertDatabaseMissing('destination_images', ['id' => $gallery->id]);
        $this->assertSame('New title', $attraction->title);
        Storage::disk('public')->assertMissing('destinations/covers/old.jpg');
        Storage::disk('public')->assertMissing('destinations/gallery/old-gallery.jpg');
        Storage::disk('public')->assertMissing('destinations/attractions/old.jpg');
        Storage::disk('public')->assertExists($destination->cover_image);
        Storage::disk('public')->assertExists($attraction->image_path);
    }

    public function test_hosting_compatible_store_endpoint_updates_a_destination_and_adds_a_tip(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.destinations.store'), $this->payload($destination->region, [
                'editing_destination_id' => $destination->id,
                'name' => 'Updated Through Save Route',
                'slug' => '',
                'travel_tips' => [
                    [
                        'title' => 'Carry sun protection',
                        'description' => 'The afternoons can be bright.',
                        'display_order' => 2,
                    ],
                ],
            ]))
            ->assertRedirect(route('admin.destinations.edit', $destination))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'name' => 'Updated Through Save Route',
            'slug' => 'updated-through-save-route',
        ]);
        $this->assertDatabaseHas('destination_travel_tips', [
            'destination_id' => $destination->id,
            'title' => 'Carry sun protection',
            'display_order' => 2,
        ]);
    }

    public function test_small_tip_only_request_adds_tip_without_changing_destination_fields(): void
    {
        $destination = Destination::factory()->create(['name' => 'Original Destination']);

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.destinations.store'), [
                '_tip_only' => true,
                'editing_destination_id' => $destination->id,
                'tip_title' => 'Respect local customs',
                'tip_description' => 'Dress appropriately at temples.',
                'tip_display_order' => 4,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Travel tip saved successfully.');

        $this->assertSame('Original Destination', $destination->fresh()->name);
        $this->assertDatabaseHas('destination_travel_tips', [
            'destination_id' => $destination->id,
            'title' => 'Respect local customs',
            'display_order' => 4,
        ]);
    }

    public function test_destination_sections_can_be_saved_independently(): void
    {
        $destination = Destination::factory()->create([
            'name' => 'Keep This Name',
            'best_time_to_visit' => 'Old season',
            'latitude' => 6.1234567,
            'longitude' => 80.1234567,
        ]);
        $this->actingAs($this->superAdmin());

        $this->postJson(route('admin.destinations.section', $destination), [
            'section' => 'map',
            'best_time_to_visit' => '',
            'latitude' => '',
            'longitude' => '',
        ])->assertOk()->assertJsonPath('message', 'Section saved successfully.');

        $destination->refresh();
        $this->assertSame('Keep This Name', $destination->name);
        $this->assertNull($destination->best_time_to_visit);
        $this->assertNull($destination->latitude);
        $this->assertNull($destination->longitude);

        $this->postJson(route('admin.destinations.section', $destination), [
            'section' => 'travel_tips',
            'travel_tips' => [[
                'title' => 'Independent tip',
                'description' => '',
                'display_order' => 3,
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('destination_travel_tips', [
            'destination_id' => $destination->id,
            'title' => 'Independent tip',
            'description' => null,
            'display_order' => 3,
        ]);
    }

    public function test_compact_payload_updates_legacy_destination_and_allows_child_data(): void
    {
        $destination = Destination::factory()->create(['name' => 'Legacy Destination']);
        $payload = $this->payload($destination->region, [
            'name' => 'Repaired Legacy Destination',
            'slug' => '',
            'best_time_to_visit' => '',
            'latitude' => '',
            'longitude' => '',
            'meta_description' => '',
            'travel_tips' => [
                [
                    'title' => 'Legacy record tip',
                    'description' => 'Saved from one compact payload.',
                    'display_order' => 7,
                ],
            ],
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.destinations.store'), [
                'editing_destination_id' => $destination->id,
                '_destination_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            ])
            ->assertRedirect(route('admin.destinations.edit', $destination))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'name' => 'Repaired Legacy Destination',
            'best_time_to_visit' => null,
            'latitude' => null,
            'longitude' => null,
            'meta_description' => null,
        ]);
        $this->assertDatabaseHas('destination_travel_tips', [
            'destination_id' => $destination->id,
            'title' => 'Legacy record tip',
            'display_order' => 7,
        ]);
    }

    public function test_update_repairs_empty_decimal_values_from_legacy_database_rows(): void
    {
        $destination = Destination::factory()->create(['name' => 'Legacy Coordinates']);

        Destination::query()->whereKey($destination->id)->toBase()->update([
            'latitude' => '',
            'longitude' => '',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.destinations.store'), $this->payload($destination->region, [
                'editing_destination_id' => $destination->id,
                'name' => 'Repaired Coordinates',
                'latitude' => null,
                'longitude' => null,
            ]))
            ->assertRedirect(route('admin.destinations.edit', $destination))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'name' => 'Repaired Coordinates',
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_slug_generation_is_unique_even_when_a_record_is_trashed(): void
    {
        $region = DestinationRegion::factory()->create();
        Destination::factory()->for($region, 'region')->create(['name' => 'Galle Fort', 'slug' => 'galle-fort'])->delete();
        $this->actingAs($this->superAdmin());

        $this->post(route('admin.destinations.store'), $this->payload($region, ['name' => 'Galle Fort', 'slug' => '']))->assertRedirect();

        $this->assertDatabaseHas('destinations', ['name' => 'Galle Fort', 'slug' => 'galle-fort-2', 'deleted_at' => null]);
    }

    public function test_index_supports_search_region_featured_status_and_pagination(): void
    {
        $south = DestinationRegion::factory()->create(['name' => 'South']);
        $north = DestinationRegion::factory()->create(['name' => 'North']);
        Destination::factory()->count(16)->for($south, 'region')->create();
        Destination::factory()->for($north, 'region')->create(['name' => 'Needle Bay', 'slug' => 'needle-bay', 'is_featured' => true, 'is_active' => false]);
        $this->actingAs($this->superAdmin());

        $this->get(route('admin.destinations.index'))->assertOk()->assertSee('page=2', false);
        $this->get(route('admin.destinations.index', ['search' => 'Needle', 'region' => $north->id, 'featured' => 'yes', 'status' => 'inactive']))
            ->assertOk()->assertSee('Needle Bay');
        $this->get(route('admin.destinations.index', ['region' => $south->id]))->assertOk()->assertDontSee('Needle Bay');
    }

    public function test_index_handles_legacy_destination_with_deleted_region(): void
    {
        $destination = Destination::factory()->create(['name' => 'Legacy Coast']);
        $destination->region->delete();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.destinations.index'))
            ->assertOk()
            ->assertSee('Legacy Coast')
            ->assertSee('Unassigned region');
    }

    public function test_destination_can_be_soft_deleted_and_restored_without_deleting_files(): void
    {
        $destination = Destination::factory()->create(['cover_image' => 'destinations/covers/keep.jpg']);
        Storage::disk('public')->put($destination->cover_image, 'image');
        $this->actingAs($this->superAdmin());

        $this->delete(route('admin.destinations.destroy', $destination))->assertRedirect()->assertSessionHas('success');
        $this->assertSoftDeleted($destination);
        Storage::disk('public')->assertExists($destination->cover_image);

        $this->patch(route('admin.destinations.restore', $destination))->assertRedirect()->assertSessionHas('success');
        $this->assertNotSoftDeleted($destination);
        Storage::disk('public')->assertExists($destination->cover_image);
    }

    public function test_policy_and_route_middleware_reject_unauthorized_access(): void
    {
        $destination = Destination::factory()->create();
        $this->get(route('admin.destinations.index'))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('admin.destinations.edit', $destination))->assertForbidden();
    }

    public function test_destination_validation_rejects_invalid_images_and_coordinates(): void
    {
        $region = DestinationRegion::factory()->create();
        $this->actingAs($this->superAdmin())
            ->post(route('admin.destinations.store'), $this->payload($region, [
                'cover_image' => UploadedFile::fake()->create('document.pdf', 50, 'application/pdf'),
                'latitude' => 100,
                'longitude' => 200,
            ]))
            ->assertSessionHasErrors(['cover_image', 'latitude', 'longitude']);
    }

    public function test_create_form_contains_all_nine_sections_and_repeaters(): void
    {
        DestinationRegion::factory()->create();
        $this->actingAs($this->superAdmin())
            ->get(route('admin.destinations.create'))
            ->assertOk()
            ->assertSee('1. Basic Information')->assertSee('2. Cover Image')->assertSee('3. Gallery')
            ->assertSee('4. Attractions')->assertSee('5. Things to Do')->assertSee('6. Travel Tips')
            ->assertSee('7. Map and Best Time')->assertSee('8. SEO')->assertSee('9. Publishing Settings')
            ->assertSee('multipart/form-data', false);
    }

    public function test_uploaded_destination_image_uses_a_host_independent_public_url(): void
    {
        $destination = Destination::factory()->create(['cover_image' => 'destinations/covers/mirissa.jpg']);
        $this->actingAs($this->superAdmin());

        $this->withServerVariables(['HTTP_HOST' => '127.0.0.1:8000'])
            ->get(route('admin.destinations.edit', $destination))
            ->assertOk()
            ->assertSee('src="/storage/destinations/covers/mirissa.jpg"', false);
    }

    private function payload(DestinationRegion $region, array $overrides = []): array
    {
        return array_replace([
            'destination_region_id' => $region->id,
            'name' => 'Ella Highlands',
            'slug' => '',
            'short_description' => 'A mountain destination.',
            'full_description' => 'Complete destination content.',
            'best_time_to_visit' => 'January to April',
            'latitude' => 6.8667,
            'longitude' => 81.0466,
            'is_featured' => 1,
            'is_active' => 1,
            'display_order' => 10,
            'meta_title' => 'Visit Ella',
            'meta_description' => 'Discover Ella.',
        ], $overrides);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
