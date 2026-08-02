<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\TeamMember;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_available_and_use_website_settings(): void
    {
        WebsiteSetting::create([
            'website_name' => 'Sri Soul Test',
            'primary_phone' => '+94 11 234 5678',
            'primary_email' => 'hello@example.test',
            'whatsapp_number' => '+94 77 123 4567',
            'footer_description' => 'Locally designed journeys.',
        ]);

        foreach (['/', '/experiences', '/packages', '/destinations', '/custom-tours', '/about-us', '/contact'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('Sri Soul Test')
                ->assertSee('Locally designed journeys.');
        }

        $this->get('/contact')
            ->assertSee('+94 11 234 5678')
            ->assertSee('hello@example.test')
            ->assertSee('wa.me/94771234567', false);
    }

    public function test_indexes_show_only_active_records(): void
    {
        $activeDestination = Destination::factory()->create(['name' => 'Visible Destination', 'is_active' => true]);
        Destination::factory()->create(['name' => 'Hidden Destination', 'is_active' => false]);

        $activeExperience = Experience::factory()->create(['title' => 'Visible Experience', 'is_active' => true]);
        Experience::factory()->create(['title' => 'Hidden Experience', 'is_active' => false]);

        $activePackage = Package::factory()->create(['title' => 'Visible Package', 'is_active' => true]);
        Package::factory()->create(['title' => 'Hidden Package', 'is_active' => false]);

        $this->get(route('destinations.index'))
            ->assertSee($activeDestination->name)
            ->assertDontSee('Hidden Destination');

        $this->get(route('experiences.index'))
            ->assertSee($activeExperience->title)
            ->assertDontSee('Hidden Experience');

        $this->get(route('packages.index'))
            ->assertSee($activePackage->title)
            ->assertDontSee('Hidden Package');
    }

    public function test_slug_routes_render_active_records_and_reject_inactive_records(): void
    {
        $activeDestination = Destination::factory()->create(['slug' => 'active-destination', 'is_active' => true]);
        $inactiveDestination = Destination::factory()->create(['slug' => 'inactive-destination', 'is_active' => false]);
        $activeExperience = Experience::factory()->create(['slug' => 'active-experience', 'is_active' => true]);
        $inactiveExperience = Experience::factory()->create(['slug' => 'inactive-experience', 'is_active' => false]);
        $activePackage = Package::factory()->create(['slug' => 'active-package', 'is_active' => true]);
        $inactivePackage = Package::factory()->create(['slug' => 'inactive-package', 'is_active' => false]);

        $this->get(route('destinations.show', $activeDestination))->assertOk();
        $this->get(route('experiences.show', $activeExperience))->assertOk();
        $this->get(route('packages.show', $activePackage))->assertOk();

        $this->get('/destinations/'.$inactiveDestination->slug)->assertNotFound();
        $this->get('/experiences/'.$inactiveExperience->slug)->assertNotFound();
        $this->get('/packages/'.$inactivePackage->slug)->assertNotFound();
    }

    public function test_page_content_and_team_members_are_limited_to_active_records(): void
    {
        PageSection::create([
            'page_key' => 'about',
            'section_key' => 'hero',
            'heading' => 'Active About Heading',
            'is_active' => true,
        ]);
        PageSection::create([
            'page_key' => 'about',
            'section_key' => 'hidden',
            'heading' => 'Inactive About Heading',
            'is_active' => false,
        ]);
        TeamMember::create([
            'name' => 'Visible Guide',
            'designation' => 'Travel Designer',
            'is_active' => true,
        ]);
        TeamMember::create([
            'name' => 'Hidden Guide',
            'designation' => 'Travel Designer',
            'is_active' => false,
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Active About Heading')
            ->assertDontSee('Inactive About Heading')
            ->assertSee('Visible Guide')
            ->assertDontSee('Hidden Guide');
    }

    public function test_public_images_have_accessible_alternative_text(): void
    {
        $destination = Destination::factory()->create([
            'name' => 'Ella',
            'cover_image' => 'destinations/ella.jpg',
            'is_active' => true,
        ]);

        $this->get(route('destinations.index'))
            ->assertOk()
            ->assertSee('alt="Ella"', false);
    }
}
