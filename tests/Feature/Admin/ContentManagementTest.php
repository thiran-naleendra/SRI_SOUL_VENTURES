<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\PageSection;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WebsiteSetting;
use Database\Seeders\PageSectionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_website_settings_create_singleton_and_safely_replace_files(): void
    {
        $this->actingAs($this->superAdmin());
        $this->put(route('admin.settings.update'), ['website_name' => 'Sri Soul', 'primary_email' => 'hello@example.com', 'logo' => UploadedFile::fake()->image('logo.png')])->assertRedirect()->assertSessionHas('success');
        $setting = WebsiteSetting::firstOrFail();
        $this->assertSame('Sri Soul', $setting->website_name);
        Storage::disk('public')->assertExists($setting->logo);
        $old = $setting->logo;
        $this->put(route('admin.settings.update'), ['website_name' => 'Sri Soul Ventures', 'logo' => UploadedFile::fake()->image('new.png')])->assertRedirect();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($setting->fresh()->logo);
        $this->assertSame(1, WebsiteSetting::count());
    }

    public function test_page_sections_support_safe_json_crud_filters_and_reject_code(): void
    {
        $this->actingAs($this->superAdmin());
        $payload = ['page_key' => 'home', 'section_key' => 'intro', 'heading' => 'Welcome', 'content' => 'Safe content', 'settings_json' => '{"columns":2}', 'display_order' => 20, 'is_active' => 1, 'image' => UploadedFile::fake()->image('intro.jpg')];
        $this->post(route('admin.pages.store'), $payload)->assertRedirect(route('admin.pages.index'));
        $section = PageSection::where('section_key', 'intro')->firstOrFail();
        $this->assertSame(['columns' => 2], $section->settings);
        Storage::disk('public')->assertExists($section->image_path);
        $this->get(route('admin.pages.index', ['search' => 'Welcome', 'page' => 'home']))->assertOk()->assertSee('Welcome');
        $this->put(route('admin.pages.update', $section), array_replace($payload, ['heading' => 'Updated', 'image' => UploadedFile::fake()->image('new.jpg')]))->assertRedirect();
        $this->assertSame('Updated', $section->fresh()->heading);
        Storage::disk('public')->assertMissing($section->image_path);
        $this->post(route('admin.pages.store'), array_replace($payload, ['section_key' => 'unsafe', 'content' => '@php echo(1); @endphp']))->assertSessionHasErrors('content');
        $this->post(route('admin.pages.store'), array_replace($payload, ['section_key' => 'bad-page', 'page_key' => 'arbitrary']))->assertSessionHasErrors('page_key');
        $path = $section->fresh()->image_path;
        $this->delete(route('admin.pages.destroy', $section))->assertRedirect();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_testimonials_have_upload_crud_and_soft_delete_restore(): void
    {
        $this->actingAs($this->superAdmin());
        $this->post(route('admin.testimonials.store'), ['customer_name' => 'Alex', 'country' => 'UK', 'testimonial' => 'Wonderful', 'rating' => 5, 'trip_name' => 'Island Tour', 'customer_image' => UploadedFile::fake()->image('alex.jpg'), 'display_order' => 1, 'is_featured' => 1, 'is_active' => 1])->assertRedirect();
        $item = Testimonial::firstOrFail();
        Storage::disk('public')->assertExists($item->customer_image);
        $this->put(route('admin.testimonials.update', $item->id), ['customer_name' => 'Alexandra', 'testimonial' => 'Excellent', 'rating' => 4, 'display_order' => 2, 'is_featured' => 0, 'is_active' => 1])->assertRedirect();
        $this->delete(route('admin.testimonials.destroy', $item->id))->assertRedirect();
        $this->assertSoftDeleted($item);
        $this->patch(route('admin.testimonials.restore', $item->id))->assertRedirect();
        $this->assertNotSoftDeleted($item);
    }

    public function test_team_members_and_faqs_support_crud_search_pagination_and_restore(): void
    {
        $this->actingAs($this->superAdmin());
        $this->post(route('admin.team-members.store'), ['name' => 'Nimal', 'designation' => 'Guide', 'biography' => 'Bio', 'email' => 'nimal@example.com', 'profile_image' => UploadedFile::fake()->image('nimal.jpg'), 'display_order' => 1, 'is_active' => 1])->assertRedirect();
        $member = TeamMember::firstOrFail();
        Storage::disk('public')->assertExists($member->profile_image);
        $this->get(route('admin.team-members.index', ['search' => 'Nimal']))->assertOk()->assertSee('Nimal');
        foreach (range(1, 21) as $i) {
            Faq::create(['category' => 'General', 'question' => "Question $i", 'answer' => 'Answer', 'display_order' => $i, 'is_active' => true]);
        }
        $this->get(route('admin.faqs.index'))->assertOk()->assertSee('page=2', false);
        $faq = Faq::first();
        $this->put(route('admin.faqs.update', $faq->id), ['category' => 'Travel', 'question' => 'Updated question', 'answer' => 'Updated answer', 'display_order' => 1, 'is_active' => 0])->assertRedirect();
        $this->delete(route('admin.faqs.destroy', $faq->id))->assertRedirect();
        $this->assertSoftDeleted($faq);
        $this->patch(route('admin.faqs.restore', $faq->id))->assertRedirect();
    }

    public function test_predefined_section_seeder_and_permissions(): void
    {
        $this->seed(PageSectionSeeder::class);
        $this->seed(PageSectionSeeder::class);
        $this->assertSame(29, PageSection::count());
        $this->assertEqualsCanonicalizing(['about', 'contact', 'custom_tours', 'destinations', 'experiences', 'home', 'packages'], PageSection::distinct()->pluck('page_key')->all());
        $this->assertEqualsCanonicalizing(
            ['hero', 'travel_vibes', 'popular_experiences', 'popular_packages', 'popular_destinations', 'why_us', 'statistics', 'testimonials', 'custom_journey_cta', 'whatsapp_cta'],
            PageSection::where('page_key', 'home')->pluck('section_key')->all(),
        );
        $this->get(route('admin.settings.index'))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('admin.pages.index'))->assertForbidden();
    }

    public function test_upload_validation_is_enforced(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.team-members.store'), ['name' => 'Bad', 'designation' => 'Test', 'profile_image' => UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf'), 'display_order' => 0, 'is_active' => 1])->assertSessionHasErrors('profile_image');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
