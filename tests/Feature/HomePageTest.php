<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\Testimonial;
use App\Models\TravelStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_editable_sections_and_renders_all_dynamic_content(): void
    {
        $this->section('hero', [
            'heading' => 'Find your wild Sri Lanka soul',
            'content' => 'A hero description from the database.',
            'button_text' => 'Plan My Test Journey',
            'button_url' => '/custom-tours',
            'settings' => [
                'highlighted_text' => 'Sri Lanka soul',
                'secondary_button_text' => 'Explore Test Experiences',
                'secondary_button_url' => '/experiences',
            ],
        ]);
        $this->section('custom_journey_cta', [
            'heading' => 'A custom CTA heading',
            'content' => 'Custom CTA content.',
            'button_text' => 'Build it',
            'button_url' => '/custom-tours',
        ]);
        $this->section('whatsapp_cta', [
            'heading' => 'Chat with a specialist',
            'content' => 'WhatsApp CTA content.',
            'button_text' => 'Chat now',
            'button_url' => 'https://wa.me/94123456789',
        ]);
        $this->section('why_us', [
            'heading' => 'Why choose the test team',
            'settings' => ['items' => [['icon' => '✓', 'title' => 'Local test insight', 'text' => 'A reason from JSON.']]],
        ]);
        $this->section('statistics', [
            'heading' => 'Test statistics',
            'settings' => ['items' => [['value' => '99+', 'label' => 'Test journeys']]],
        ]);

        $style = TravelStyle::factory()->create(['name' => 'Slow Travel', 'slug' => 'slow-travel', 'is_active' => true]);
        TravelStyle::factory()->create(['name' => 'Hidden Style', 'is_active' => false]);

        $experience = Experience::factory()->create([
            'title' => 'Popular Test Experience',
            'cover_image' => 'experiences/popular.jpg',
            'is_popular' => true,
            'is_featured' => false,
            'is_active' => true,
        ]);
        $experience->travelStyles()->attach($style);
        Experience::factory()->create(['title' => 'Not Popular Experience', 'is_popular' => false, 'is_featured' => true]);

        Package::factory()->create(['title' => 'Featured Test Package', 'is_featured' => true, 'is_popular' => false]);
        Destination::factory()->create(['name' => 'Featured Test Destination', 'is_featured' => true]);
        Testimonial::factory()->create(['customer_name' => 'Featured Test Guest', 'is_featured' => true, 'is_active' => true]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeInOrder(['Find your wild', 'Sri Lanka soul'])
            ->assertSee('hero-highlight', false)
            ->assertSee('A hero description from the database.')
            ->assertSee('Plan My Test Journey')
            ->assertSee('Explore Test Experiences')
            ->assertSee('Slow Travel')
            ->assertDontSee('Hidden Style')
            ->assertSee('travel_style=slow-travel', false)
            ->assertSee('Popular Test Experience')
            ->assertDontSee('Not Popular Experience')
            ->assertSee('Featured Test Package')
            ->assertSee('Featured Test Destination')
            ->assertSee('Featured Test Guest')
            ->assertSee('Local test insight')
            ->assertSee('A reason from JSON.')
            ->assertSee('99+')
            ->assertSee('A custom CTA heading')
            ->assertSee('Chat with a specialist')
            ->assertSee('loading="lazy"', false);
    }

    public function test_home_page_excludes_inactive_and_non_qualifying_records(): void
    {
        Experience::factory()->create(['title' => 'Inactive Popular Experience', 'is_popular' => true, 'is_active' => false]);
        Package::factory()->create(['title' => 'Inactive Featured Package', 'is_featured' => true, 'is_active' => false]);
        Package::factory()->create(['title' => 'Ordinary Active Package', 'is_featured' => false, 'is_popular' => false, 'is_active' => true]);
        Destination::factory()->create(['name' => 'Inactive Featured Destination', 'is_featured' => true, 'is_active' => false]);
        Destination::factory()->create(['name' => 'Ordinary Active Destination', 'is_featured' => false, 'is_active' => true]);
        Testimonial::factory()->create(['customer_name' => 'Inactive Featured Guest', 'is_featured' => true, 'is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Inactive Popular Experience')
            ->assertDontSee('Inactive Featured Package')
            ->assertDontSee('Ordinary Active Package')
            ->assertDontSee('Inactive Featured Destination')
            ->assertDontSee('Ordinary Active Destination')
            ->assertDontSee('Inactive Featured Guest')
            ->assertSee('Popular experiences are coming soon')
            ->assertSee('Curated packages are coming soon')
            ->assertSee('Featured destinations are coming soon')
            ->assertSee('Traveller stories are coming soon');
    }

    public function test_travel_style_links_filter_active_experiences_and_packages(): void
    {
        $style = TravelStyle::factory()->create(['slug' => 'wellness', 'is_active' => true]);
        $otherStyle = TravelStyle::factory()->create(['slug' => 'adventure', 'is_active' => true]);

        $matchingExperience = Experience::factory()->create(['title' => 'Wellness Experience']);
        $matchingExperience->travelStyles()->attach($style);
        $otherExperience = Experience::factory()->create(['title' => 'Adventure Experience']);
        $otherExperience->travelStyles()->attach($otherStyle);

        $matchingPackage = Package::factory()->create(['title' => 'Wellness Package']);
        $matchingPackage->travelStyles()->attach($style);
        $otherPackage = Package::factory()->create(['title' => 'Adventure Package']);
        $otherPackage->travelStyles()->attach($otherStyle);

        $this->get(route('experiences.index', ['travel_style' => 'wellness']))
            ->assertOk()
            ->assertSee($matchingExperience->title)
            ->assertDontSee($otherExperience->title);

        $this->get(route('packages.index', ['travel_style' => 'wellness']))
            ->assertOk()
            ->assertSee($matchingPackage->title)
            ->assertDontSee($otherPackage->title);
    }

    private function section(string $key, array $attributes): PageSection
    {
        return PageSection::create([
            'page_key' => 'home',
            'section_key' => $key,
            'display_order' => 10,
            'is_active' => true,
            ...$attributes,
        ]);
    }
}
