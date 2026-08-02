<?php

namespace Tests\Feature\Admin;

use App\Models\Destination;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\ExperienceImage;
use App\Models\TravelStyle;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExperienceCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_complete_experience_with_media_and_children(): void
    {
        [$category, $destination, $styles] = $this->relations();
        $this->actingAs($this->superAdmin());

        $this->post(route('admin.experiences.store'), $this->payload($category, $destination, [
            'travel_style_ids' => $styles->pluck('id')->all(),
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'gallery' => [
                ['image' => UploadedFile::fake()->image('later.jpg'), 'alt_text' => 'Later', 'display_order' => 20],
                ['image' => UploadedFile::fake()->image('first.png'), 'alt_text' => 'First', 'display_order' => 10],
            ],
            'highlights' => [['item' => 'Private guide', 'display_order' => 1]],
            'inclusions' => [['item' => 'Lunch', 'display_order' => 1]],
            'exclusions' => [['item' => 'Tips', 'display_order' => 1]],
        ]))->assertRedirect(route('admin.experiences.index'))->assertSessionHas('success');

        $experience = Experience::where('slug', 'ella-sunrise-hike')->firstOrFail();
        $this->assertCount(2, $experience->travelStyles);
        $this->assertCount(2, $experience->images);
        $this->assertSame('First', $experience->images->first()->alt_text);
        $this->assertSame('Private guide', $experience->highlights->first()->item);
        $this->assertSame('Lunch', $experience->inclusions->first()->item);
        $this->assertSame('Tips', $experience->exclusions->first()->item);
        Storage::disk('public')->assertExists($experience->cover_image);
        $experience->images->each(fn (ExperienceImage $image) => Storage::disk('public')->assertExists($image->image_path));
    }

    public function test_update_replaces_cover_removes_and_reorders_gallery_and_syncs_styles(): void
    {
        [$category, $destination, $styles] = $this->relations();
        $experience = Experience::factory()->create(['experience_category_id' => $category->id, 'destination_id' => $destination->id, 'cover_image' => 'experiences/covers/old.jpg']);
        $remove = ExperienceImage::create(['experience_id' => $experience->id, 'image_path' => 'experiences/gallery/remove.jpg', 'display_order' => 1]);
        $keep = ExperienceImage::create(['experience_id' => $experience->id, 'image_path' => 'experiences/gallery/keep.jpg', 'display_order' => 2]);
        foreach ([$experience->cover_image, $remove->image_path, $keep->image_path] as $path) {
            Storage::disk('public')->put($path, 'old');
        }
        $experience->travelStyles()->sync($styles->pluck('id'));
        $this->actingAs($this->superAdmin());

        $this->put(route('admin.experiences.update', $experience), $this->payload($category, $destination, [
            'title' => 'Updated Experience', 'slug' => '', 'travel_style_ids' => [$styles->first()->id],
            'cover_image' => UploadedFile::fake()->image('new.jpg'),
            'gallery' => [
                ['id' => $remove->id, '_remove' => 1, 'display_order' => 1],
                ['id' => $keep->id, 'alt_text' => 'Kept', 'display_order' => 50],
            ],
        ]))->assertRedirect(route('admin.experiences.index'));

        $experience->refresh();
        $this->assertSame('updated-experience', $experience->slug);
        $this->assertCount(1, $experience->travelStyles);
        $this->assertDatabaseMissing('experience_images', ['id' => $remove->id]);
        $this->assertDatabaseHas('experience_images', ['id' => $keep->id, 'display_order' => 50]);
        Storage::disk('public')->assertMissing('experiences/covers/old.jpg');
        Storage::disk('public')->assertMissing('experiences/gallery/remove.jpg');
        Storage::disk('public')->assertExists('experiences/gallery/keep.jpg');
        Storage::disk('public')->assertExists($experience->cover_image);
    }

    public function test_slug_is_unique_across_soft_deleted_experiences(): void
    {
        [$category, $destination] = $this->relations();
        Experience::factory()->create(['experience_category_id' => $category->id, 'destination_id' => $destination->id, 'title' => 'Tea Journey', 'slug' => 'tea-journey'])->delete();
        $this->actingAs($this->superAdmin());

        $this->post(route('admin.experiences.store'), $this->payload($category, $destination, ['title' => 'Tea Journey', 'slug' => '']))->assertRedirect();
        $this->assertDatabaseHas('experiences', ['slug' => 'tea-journey-2', 'deleted_at' => null]);
    }

    public function test_index_supports_all_filters_and_pagination(): void
    {
        [$category, $destination] = $this->relations();
        $otherCategory = ExperienceCategory::factory()->create();
        Experience::factory()->count(16)->create(['experience_category_id' => $otherCategory->id, 'destination_id' => $destination->id]);
        Experience::factory()->create(['experience_category_id' => $category->id, 'destination_id' => $destination->id, 'title' => 'Needle Safari', 'slug' => 'needle-safari', 'is_featured' => true, 'is_active' => false]);
        $this->actingAs($this->superAdmin());

        $this->get(route('admin.experiences.index'))->assertOk()->assertSee('page=2', false);
        $this->get(route('admin.experiences.index', ['search' => 'Needle', 'category' => $category->id, 'destination' => $destination->id, 'featured' => 'yes', 'status' => 'inactive']))->assertOk()->assertSee('Needle Safari');
        $this->get(route('admin.experiences.index', ['category' => $otherCategory->id]))->assertOk()->assertDontSee('Needle Safari');
    }

    public function test_soft_delete_and_restore_preserve_images(): void
    {
        $experience = Experience::factory()->create(['cover_image' => 'experiences/covers/keep.jpg']);
        Storage::disk('public')->put($experience->cover_image, 'image');
        $this->actingAs($this->superAdmin());

        $this->delete(route('admin.experiences.destroy', $experience))->assertRedirect();
        $this->assertSoftDeleted($experience);
        Storage::disk('public')->assertExists($experience->cover_image);
        $this->patch(route('admin.experiences.restore', $experience))->assertRedirect();
        $this->assertNotSoftDeleted($experience);
    }

    public function test_policy_validation_and_image_rules_are_enforced(): void
    {
        [$category, $destination] = $this->relations();
        $experience = Experience::factory()->create(['experience_category_id' => $category->id, 'destination_id' => $destination->id]);
        $this->get(route('admin.experiences.index'))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('admin.experiences.edit', $experience))->assertForbidden();

        $this->actingAs($this->superAdmin())->post(route('admin.experiences.store'), $this->payload($category, $destination, [
            'cover_image' => UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf'), 'currency' => 'US', 'latitude' => 99,
        ]))->assertSessionHasErrors(['cover_image', 'currency', 'latitude']);
    }

    public function test_create_form_contains_all_nine_sections(): void
    {
        $this->relations();
        $this->actingAs($this->superAdmin())->get(route('admin.experiences.create'))->assertOk()
            ->assertSee('1. Basic Information')->assertSee('2. Destination and Travel Styles')->assertSee('3. Images')
            ->assertSee('4. Highlights')->assertSee('5. Inclusions')->assertSee('6. Exclusions')
            ->assertSee('7. Location and Pricing')->assertSee('8. SEO')->assertSee('9. Publishing')
            ->assertSee('travel_style_ids[]', false)->assertSee('multipart/form-data', false);
    }

    private function relations(): array
    {
        return [ExperienceCategory::factory()->create(), Destination::factory()->create(), TravelStyle::factory()->count(2)->create()];
    }

    private function payload(ExperienceCategory $category, Destination $destination, array $overrides = []): array
    {
        return array_replace([
            'experience_category_id' => $category->id, 'destination_id' => $destination->id,
            'title' => 'Ella Sunrise Hike', 'slug' => '', 'badge_text' => 'Popular',
            'short_description' => 'A sunrise walk.', 'full_description' => 'Complete experience details.',
            'location' => 'Ella', 'duration_value' => 4, 'duration_unit' => 'hours',
            'starting_price' => 125.50, 'currency' => 'usd', 'latitude' => 6.8667, 'longitude' => 81.0466,
            'important_information' => 'Bring walking shoes.', 'is_featured' => 1, 'is_popular' => 1,
            'is_active' => 1, 'display_order' => 10, 'meta_title' => 'Ella Sunrise Hike',
            'meta_description' => 'Hike Ella at sunrise.',
        ], $overrides);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
