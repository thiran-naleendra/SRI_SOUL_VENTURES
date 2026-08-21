<?php

namespace Tests\Feature\Admin;

use App\Models\Destination;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageImage;
use App\Models\PackageInclusion;
use App\Models\TravelStyle;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageCrudTest extends TestCase
{
    public function test_admin_package_show_url_redirects_to_the_edit_form(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $package = Package::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.packages.show', $package))
            ->assertRedirect(route('admin.packages.edit', $package));
    }

    public function test_edit_form_uses_shared_host_compatible_post_save_route(): void
    {
        $package = Package::factory()->create();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.packages.edit', $package))
            ->assertOk()
            ->assertSee('action="'.route('admin.packages.save', $package).'"', false)
            ->assertDontSee('name="_method" value="PUT"', false);
    }

    public function test_edit_form_has_independent_section_save_endpoint(): void
    {
        $package = Package::factory()->create();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.packages.edit', $package))
            ->assertOk()
            ->assertSee('data-package-section-url="'.route('admin.packages.section', $package).'"', false)
            ->assertSee('data-package-section="basic"', false)
            ->assertSee('Save this section');
    }

    public function test_basic_section_updates_only_basic_data_and_saves_blank_values_as_null(): void
    {
        [$category] = $this->relations();
        $package = Package::factory()->create([
            'package_category_id' => $category->id,
            'accommodation_summary' => 'Must remain unchanged',
            'discount_price' => 50,
        ]);

        $payload = $this->payload($category, [
            'section' => 'basic',
            'title' => 'Section Updated Tour',
            'discount_price' => '',
        ]);

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.packages.section', $package), $payload)
            ->assertOk()
            ->assertJsonPath('message', 'Package section saved successfully.');

        $package->refresh();
        $this->assertSame('Section Updated Tour', $package->title);
        $this->assertNull($package->discount_price);
        $this->assertSame('Must remain unchanged', $package->accommodation_summary);
    }

    public function test_items_section_removes_and_reorders_without_updating_package_fields(): void
    {
        $package = Package::factory()->create(['title' => 'Keep this title']);
        $removed = PackageInclusion::create(['package_id' => $package->id, 'item' => 'Remove me', 'display_order' => 1]);
        $kept = PackageInclusion::create(['package_id' => $package->id, 'item' => 'Keep me', 'display_order' => 2]);

        $this->actingAs($this->superAdmin())->postJson(route('admin.packages.section', $package), [
            'section' => 'items',
            'inclusions' => [
                ['id' => $removed->id, 'item' => $removed->item, 'display_order' => 1, '_remove' => 1],
                ['id' => $kept->id, 'item' => $kept->item, 'display_order' => 9],
            ],
        ])->assertOk();

        $this->assertDatabaseMissing('package_inclusions', ['id' => $removed->id]);
        $this->assertDatabaseHas('package_inclusions', ['id' => $kept->id, 'display_order' => 9]);
        $this->assertSame('Keep this title', $package->fresh()->title);
    }

    public function test_legacy_package_post_url_is_accepted(): void
    {
        [$category] = $this->relations();
        $package = Package::factory()->create(['package_category_id' => $category->id]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.packages.update', $package), $this->payload($category))
            ->assertRedirect(route('admin.packages.index'));
    }

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_create_persists_all_relationships_and_files(): void
    {
        [$category, $destinations, $styles] = $this->relations();
        $this->actingAs($this->superAdmin());
        $data = $this->payload($category, [
            'destination_ids' => $destinations->pluck('id')->all(), 'travel_style_ids' => $styles->pluck('id')->all(),
            'cover_image' => UploadedFile::fake()->image('cover.jpg'), 'itinerary_pdf' => UploadedFile::fake()->create('plan.pdf', 200, 'application/pdf'),
            'gallery' => [['image' => UploadedFile::fake()->image('gallery.jpg'), 'alt_text' => 'Gallery', 'caption' => 'Caption', 'display_order' => 2]],
            'itineraries' => [['day_number' => 1, 'title' => 'Arrival', 'description' => 'Welcome', 'destination_name' => 'Colombo', 'accommodation_name' => 'Hotel', 'meals' => 'Dinner', 'image' => UploadedFile::fake()->image('day.jpg'), 'display_order' => 1]],
            'highlights' => [['title' => 'Private guide', 'image' => UploadedFile::fake()->image('highlight.jpg'), 'alt_text' => 'Guide', 'display_order' => 1]],
            'inclusions' => [['item' => 'Accommodation', 'display_order' => 1]], 'exclusions' => [['item' => 'Flights', 'display_order' => 1]],
            'faqs' => [['question' => 'Is it private?', 'answer' => 'Yes', 'is_active' => 1, 'display_order' => 1]],
            'reviews' => [['customer_name' => 'Alex', 'country' => 'UK', 'rating' => 5, 'review' => 'Excellent', 'customer_image' => UploadedFile::fake()->image('alex.jpg'), 'is_approved' => 1, 'display_order' => 1]],
        ]);
        $this->post(route('admin.packages.store'), $data)->assertRedirect(route('admin.packages.index'))->assertSessionHas('success');
        $package = Package::where('slug', 'complete-island-journey')->firstOrFail();
        $this->assertCount(2, $package->destinations);
        $this->assertCount(2, $package->travelStyles);
        $this->assertCount(1, $package->images);
        $this->assertCount(1, $package->itineraries);
        $this->assertCount(1, $package->highlights);
        $this->assertCount(1, $package->inclusions);
        $this->assertCount(1, $package->exclusions);
        $this->assertCount(1, $package->faqs);
        $this->assertCount(1, $package->reviews);
        Storage::disk('public')->assertExists($package->cover_image);
        Storage::disk('public')->assertExists($package->itinerary_pdf);
        Storage::disk('public')->assertExists($package->images->first()->image_path);
        Storage::disk('public')->assertExists($package->itineraries->first()->image_path);
        Storage::disk('public')->assertExists($package->highlights->first()->image_path);
        Storage::disk('public')->assertExists($package->reviews->first()->customer_image);
    }

    public function test_update_replaces_primary_files_removes_gallery_and_syncs_pivots(): void
    {
        [$category, $destinations, $styles] = $this->relations();
        $package = Package::factory()->create(['package_category_id' => $category->id, 'cover_image' => 'packages/covers/old.jpg', 'itinerary_pdf' => 'packages/pdfs/old.pdf']);
        $image = PackageImage::create(['package_id' => $package->id, 'image_path' => 'packages/gallery/old.jpg']);
        foreach ([$package->cover_image, $package->itinerary_pdf, $image->image_path] as $path) {
            Storage::disk('public')->put($path, 'old');
        }
        $package->destinations()->sync($destinations->pluck('id'));
        $package->travelStyles()->sync($styles->pluck('id'));
        $this->actingAs($this->superAdmin());
        $this->put(route('admin.packages.update', $package), $this->payload($category, [
            'title' => 'Updated Tour', 'slug' => '', 'destination_ids' => [$destinations->first()->id], 'travel_style_ids' => [$styles->first()->id],
            'cover_image' => UploadedFile::fake()->image('new.jpg'), 'itinerary_pdf' => UploadedFile::fake()->create('new.pdf', 20, 'application/pdf'),
            'gallery' => [['id' => $image->id, '_remove' => 1, 'display_order' => 0]],
        ]))->assertRedirect(route('admin.packages.index'));
        $package->refresh();
        $this->assertSame('updated-tour', $package->slug);
        $this->assertCount(1, $package->destinations);
        $this->assertCount(1, $package->travelStyles);
        $this->assertDatabaseMissing('package_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing('packages/covers/old.jpg');
        Storage::disk('public')->assertMissing('packages/pdfs/old.pdf');
        Storage::disk('public')->assertMissing('packages/gallery/old.jpg');
        Storage::disk('public')->assertExists($package->cover_image);
        Storage::disk('public')->assertExists($package->itinerary_pdf);
    }

    public function test_post_save_removes_inclusions_and_updates_display_order(): void
    {
        [$category] = $this->relations();
        $package = Package::factory()->create(['package_category_id' => $category->id]);
        $removed = PackageInclusion::create(['package_id' => $package->id, 'item' => 'Duplicate vehicle', 'display_order' => 1]);
        $kept = PackageInclusion::create(['package_id' => $package->id, 'item' => 'Government taxes', 'display_order' => 0]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.packages.save', $package), $this->payload($category, [
                'inclusions' => [
                    ['id' => $removed->id, 'item' => $removed->item, 'display_order' => 1, '_remove' => 1],
                    ['id' => $kept->id, 'item' => $kept->item, 'display_order' => 9],
                ],
            ]))
            ->assertRedirect(route('admin.packages.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('package_inclusions', ['id' => $removed->id]);
        $this->assertDatabaseHas('package_inclusions', ['id' => $kept->id, 'display_order' => 9]);
    }

    public function test_index_filters_and_pagination_work(): void
    {
        [$category, $destinations] = $this->relations();
        $other = PackageCategory::factory()->create();
        Package::factory()->count(16)->create(['package_category_id' => $other->id]);
        $needle = Package::factory()->create(['package_category_id' => $category->id, 'title' => 'Needle Luxury Tour', 'slug' => 'needle-luxury-tour', 'is_active' => false, 'is_popular' => true, 'is_featured' => true]);
        $needle->destinations()->attach($destinations->first());
        $this->actingAs($this->superAdmin());
        $this->get(route('admin.packages.index'))->assertOk()->assertSee('page=2', false);
        $this->get(route('admin.packages.index', ['search' => 'Needle', 'category' => $category->id, 'destination' => $destinations->first()->id, 'active' => 'no', 'popular' => 'yes', 'featured' => 'yes']))->assertOk()->assertSee('Needle Luxury Tour');
        $this->get(route('admin.packages.index', ['category' => $other->id]))->assertOk()->assertDontSee('Needle Luxury Tour');
    }

    public function test_soft_delete_restore_and_policy_enforcement_work(): void
    {
        $package = Package::factory()->create(['cover_image' => 'packages/covers/keep.jpg']);
        Storage::disk('public')->put($package->cover_image, 'x');
        $this->get(route('admin.packages.index'))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('admin.packages.edit', $package))->assertForbidden();
        $this->actingAs($this->superAdmin())->delete(route('admin.packages.destroy', $package))->assertRedirect();
        $this->assertSoftDeleted($package);
        Storage::disk('public')->assertExists($package->cover_image);
        $this->patch(route('admin.packages.restore', $package))->assertRedirect();
        $this->assertNotSoftDeleted($package);
    }

    public function test_validation_rejects_invalid_pdf_image_prices_and_relationships(): void
    {
        [$category] = $this->relations();
        $this->actingAs($this->superAdmin())->post(route('admin.packages.store'), $this->payload($category, [
            'cover_image' => UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf'), 'itinerary_pdf' => UploadedFile::fake()->image('bad.jpg'),
            'starting_price' => 100, 'discount_price' => 200, 'maximum_travelers' => 1, 'minimum_travelers' => 2,
        ]))->assertSessionHasErrors(['cover_image', 'itinerary_pdf', 'discount_price', 'maximum_travelers']);
    }

    public function test_form_has_all_ten_tabs_and_multipart_uploads(): void
    {
        $this->relations();
        $this->actingAs($this->superAdmin())->get(route('admin.packages.create'))->assertOk()
            ->assertSee('1. Basic Information')->assertSee('2. Destinations &amp; Styles', false)->assertSee('3. Images')->assertSee('4. Itinerary')
            ->assertSee('5. Highlights')->assertSee('6. Inclusions &amp; Exclusions', false)->assertSee('7. Accommodation &amp; Policies', false)
            ->assertSee('8. FAQs')->assertSee('9. Reviews')->assertSee('10. SEO')->assertSee('multipart/form-data', false)
            ->assertSee('class="tab-content package-form-tabs"', false)
            ->assertSee('aria-controls="tab-images"', false);
    }

    private function relations(): array
    {
        return [PackageCategory::factory()->create(), Destination::factory()->count(2)->create(), TravelStyle::factory()->count(2)->create()];
    }

    private function payload(PackageCategory $category, array $overrides = []): array
    {
        return array_replace(['package_category_id' => $category->id, 'title' => 'Complete Island Journey', 'slug' => '', 'badge_text' => 'Best Seller', 'short_description' => 'Short copy', 'full_description' => 'Full copy', 'days' => 10, 'nights' => 9, 'starting_price' => 2500, 'discount_price' => 2200, 'currency' => 'usd', 'price_note' => 'Per person', 'minimum_travelers' => 2, 'maximum_travelers' => 12, 'tour_type' => 'Private', 'physical_level' => 'Moderate', 'perfect_for' => 'Couples', 'accommodation_summary' => 'Hotels', 'transportation_summary' => 'Private vehicle', 'cancellation_policy' => 'Terms', 'support_text' => '24/7 support', 'terms_and_conditions' => 'Conditions', 'is_featured' => 1, 'is_popular' => 1, 'is_customizable' => 1, 'is_active' => 1, 'display_order' => 10, 'meta_title' => 'Island Journey', 'meta_description' => 'Explore Sri Lanka.'], $overrides);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
