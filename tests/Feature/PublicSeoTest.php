<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_outputs_default_metadata_canonical_social_cards_and_organization_schema(): void
    {
        WebsiteSetting::create([
            'website_name' => 'Sri Soul Ventures',
            'default_meta_title' => 'Tailor-made Sri Lanka Holidays',
            'default_meta_description' => 'Private journeys created by local Sri Lanka travel experts.',
            'logo' => 'settings/logo.png',
            'primary_email' => 'hello@example.test',
            'primary_phone' => '+94 77 123 4567',
            'address' => 'Colombo, Sri Lanka',
            'instagram_url' => 'https://instagram.com/srisoul',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>Tailor-made Sri Lanka Holidays</title>', false)
            ->assertSee('<meta name="description" content="Private journeys created by local Sri Lanka travel experts.">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('<meta property="og:title" content="Tailor-made Sri Lanka Holidays">', false)
            ->assertSee('<meta property="og:image" content="', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"telephone":"+94 77 123 4567"', false)
            ->assertSee('https://instagram.com/srisoul', false);
    }

    public function test_package_and_destination_pages_output_entity_and_breadcrumb_schema(): void
    {
        $package = Package::factory()->create([
            'title' => 'Schema Journey',
            'short_description' => 'A complete island journey.',
            'cover_image' => 'packages/schema.jpg',
            'starting_price' => 1200,
            'currency' => 'USD',
        ]);
        $package->itineraries()->create(['day_number' => 1, 'title' => 'Arrive in Colombo']);
        $destination = Destination::factory()->create([
            'name' => 'Schema Ella',
            'short_description' => 'A misty hill-country destination.',
            'cover_image' => 'destinations/schema.jpg',
            'latitude' => 6.8667,
            'longitude' => 81.0466,
        ]);

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('"@type":"TouristTrip"', false)
            ->assertSee('"@type":"Offer"', false)
            ->assertSee('"priceCurrency":"USD"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('<meta property="og:type" content="article">', false);

        $this->get(route('destinations.show', $destination))
            ->assertOk()
            ->assertSee('"@type":"TouristDestination"', false)
            ->assertSee('"@type":"GeoCoordinates"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('<meta property="og:image" content="', false);
    }

    public function test_sitemap_contains_only_active_non_deleted_public_records(): void
    {
        $activeExperience = Experience::factory()->create(['slug' => 'active-experience', 'is_active' => true]);
        Experience::factory()->create(['slug' => 'inactive-experience', 'is_active' => false]);
        $deletedExperience = Experience::factory()->create(['slug' => 'deleted-experience', 'is_active' => true]);
        $deletedExperience->delete();

        $activePackage = Package::factory()->create(['slug' => 'active-package', 'is_active' => true]);
        Package::factory()->create(['slug' => 'inactive-package', 'is_active' => false]);
        $activeDestination = Destination::factory()->create(['slug' => 'active-destination', 'is_active' => true]);
        Destination::factory()->create(['slug' => 'inactive-destination', 'is_active' => false]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('experiences.show', $activeExperience), false)
            ->assertSee(route('packages.show', $activePackage), false)
            ->assertSee(route('destinations.show', $activeDestination), false)
            ->assertDontSee('inactive-experience')
            ->assertDontSee('deleted-experience')
            ->assertDontSee('inactive-package')
            ->assertDontSee('inactive-destination');
    }

    public function test_sitemap_cache_is_invalidated_when_public_content_changes(): void
    {
        $this->get(route('sitemap'))->assertDontSee('newly-published');

        $experience = Experience::factory()->create(['slug' => 'newly-published', 'is_active' => true]);

        $this->get(route('sitemap'))->assertSee(route('experiences.show', $experience), false);
    }

    public function test_settings_cache_is_invalidated_after_an_admin_style_model_update(): void
    {
        $settings = WebsiteSetting::create(['website_name' => 'Old Site Name']);
        $this->assertSame('Old Site Name', WebsiteSetting::current()?->website_name);

        $settings->update(['website_name' => 'Updated Site Name']);

        $this->assertSame('Updated Site Name', WebsiteSetting::current()?->website_name);
    }

    public function test_robots_file_allows_public_pages_and_points_to_the_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Allow: /', false)
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }
}
