<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\TravelStyle;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPackagePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_package_and_destination_content(): void
    {
        $destination = Destination::factory()->create(['name' => 'Needle Coast']);
        $match = Package::factory()->create(['title' => 'Coastal Discovery']);
        $match->destinations()->attach($destination);
        Package::factory()->create(['title' => 'Unrelated Hills']);

        $this->get(route('packages.index', ['search' => 'Needle Coast']))
            ->assertOk()->assertSee($match->title)->assertDontSee('Unrelated Hills');
    }

    public function test_every_duration_filter(): void
    {
        Package::factory()->create(['title' => 'Short Escape Test', 'days' => 3]);
        Package::factory()->create(['title' => 'Week Journey Test', 'days' => 7]);
        Package::factory()->create(['title' => 'Two Week Test', 'days' => 12]);
        Package::factory()->create(['title' => 'Extended Journey Test', 'days' => 18]);

        $this->get(route('packages.index', ['duration' => 'short']))->assertSee('Short Escape Test')->assertDontSee('Week Journey Test');
        $this->get(route('packages.index', ['duration' => 'week']))->assertSee('Week Journey Test')->assertDontSee('Two Week Test');
        $this->get(route('packages.index', ['duration' => 'two_weeks']))->assertSee('Two Week Test')->assertDontSee('Extended Journey Test');
        $this->get(route('packages.index', ['duration' => 'extended']))->assertSee('Extended Journey Test')->assertDontSee('Short Escape Test');
    }

    public function test_travel_style_and_destination_filters(): void
    {
        $style = TravelStyle::factory()->create(['slug' => 'family', 'is_active' => true]);
        $destination = Destination::factory()->create();
        $match = Package::factory()->create(['title' => 'Family Destination Match']);
        $match->travelStyles()->attach($style);
        $match->destinations()->attach($destination);
        Package::factory()->create(['title' => 'Filter Miss']);

        $this->get(route('packages.index', ['travel_style' => 'family']))->assertSee($match->title)->assertDontSee('Filter Miss');
        $this->get(route('packages.index', ['destination' => $destination->id]))->assertSee($match->title)->assertDontSee('Filter Miss');
    }

    public function test_budget_and_traveler_filters(): void
    {
        Package::factory()->create(['title' => 'Small Midrange Group', 'starting_price' => 500, 'minimum_travelers' => 2, 'maximum_travelers' => 6]);
        Package::factory()->create(['title' => 'Budget Couple', 'starting_price' => 200, 'minimum_travelers' => 2, 'maximum_travelers' => 2]);
        Package::factory()->create(['title' => 'Premium Group', 'starting_price' => 1200, 'minimum_travelers' => 4, 'maximum_travelers' => null]);

        $this->get(route('packages.index', ['budget_min' => 400, 'budget_max' => 800, 'travelers' => 5]))
            ->assertSee('Small Midrange Group')
            ->assertDontSee('Budget Couple')
            ->assertDontSee('Premium Group');
    }

    public function test_all_package_sort_options(): void
    {
        Package::factory()->create(['title' => 'Ordinary Older Long', 'is_popular' => false, 'display_order' => 1, 'starting_price' => 400, 'days' => 12, 'created_at' => now()->subDays(2)]);
        Package::factory()->create(['title' => 'Popular Middle', 'is_popular' => true, 'display_order' => 20, 'starting_price' => 200, 'days' => 7, 'created_at' => now()->subDay()]);
        Package::factory()->create(['title' => 'Newest Short Premium', 'is_popular' => false, 'display_order' => 2, 'starting_price' => 900, 'days' => 3, 'created_at' => now()]);

        $this->get(route('packages.index', ['sort' => 'popular']))->assertSeeInOrder(['Popular Middle', 'Ordinary Older Long', 'Newest Short Premium']);
        $this->get(route('packages.index', ['sort' => 'newest']))->assertSeeInOrder(['Newest Short Premium', 'Popular Middle', 'Ordinary Older Long']);
        $this->get(route('packages.index', ['sort' => 'price_asc']))->assertSeeInOrder(['Popular Middle', 'Ordinary Older Long', 'Newest Short Premium']);
        $this->get(route('packages.index', ['sort' => 'price_desc']))->assertSeeInOrder(['Newest Short Premium', 'Ordinary Older Long', 'Popular Middle']);
        $this->get(route('packages.index', ['sort' => 'duration_asc']))->assertSeeInOrder(['Newest Short Premium', 'Popular Middle', 'Ordinary Older Long']);
    }

    public function test_pagination_preserves_package_filters(): void
    {
        Package::factory()->count(13)->create(['short_description' => 'Package Pagination Needle']);

        $this->get(route('packages.index', ['search' => 'Package Pagination Needle', 'sort' => 'newest', 'budget_min' => 100]))
            ->assertOk()
            ->assertSee('search=Package%20Pagination%20Needle&amp;sort=newest&amp;budget_min=100&amp;page=2', false);
    }

    public function test_package_card_displays_required_dynamic_values_and_detail_route(): void
    {
        $destination = Destination::factory()->create(['name' => 'Kandy']);
        $package = Package::factory()->create([
            'title' => 'Card Contract Package',
            'badge_text' => 'Best Seller',
            'days' => 8,
            'nights' => 7,
            'minimum_travelers' => 3,
            'physical_level' => 'Moderate',
            'starting_price' => 999,
            'cover_image' => 'packages/card.jpg',
        ]);
        $package->destinations()->attach($destination);

        $this->get(route('packages.index'))
            ->assertOk()
            ->assertSee('Card Contract Package')
            ->assertSee('Best Seller')
            ->assertSee('8 days · 7 nights')
            ->assertSee('Kandy')
            ->assertSee('Minimum 3 travellers')
            ->assertSee('Moderate')
            ->assertSee('USD 999.00')
            ->assertSee('href="'.route('packages.show', $package).'"', false)
            ->assertSee('View Details');
    }

    public function test_package_details_render_every_dynamic_section_and_only_approved_content(): void
    {
        WebsiteSetting::create(['website_name' => 'Sri Soul', 'whatsapp_number' => '+94 77 111 2233']);
        PageSection::create(['page_key' => 'packages', 'section_key' => 'custom_journey_cta', 'heading' => 'Dynamic Package CTA', 'content' => 'Tailored CTA copy.', 'button_text' => 'Customize now', 'button_url' => '/custom-tours', 'display_order' => 1, 'is_active' => true]);
        $destination = Destination::factory()->create(['name' => 'Sigiriya']);
        $style = TravelStyle::factory()->create(['name' => 'Culture', 'is_active' => true]);
        $package = Package::factory()->create([
            'title' => 'Sri Lanka Highlights Test',
            'badge_text' => 'Signature Tour',
            'short_description' => 'Detail short description.',
            'full_description' => 'Dynamic overview description.',
            'cover_image' => 'packages/hero.jpg',
            'days' => 10,
            'nights' => 9,
            'starting_price' => 1500,
            'price_note' => 'Seasonal rates may apply.',
            'minimum_travelers' => 2,
            'maximum_travelers' => 8,
            'tour_type' => 'Private',
            'physical_level' => 'Easy',
            'perfect_for' => 'First-time visitors',
            'accommodation_summary' => 'Boutique hotels and lodges.',
            'transportation_summary' => 'Private air-conditioned vehicle.',
            'cancellation_policy' => 'Free cancellation within the stated period.',
            'support_text' => 'Round-the-clock local assistance.',
            'is_customizable' => true,
            'meta_title' => 'Dynamic Package SEO Title',
            'meta_description' => 'Dynamic package SEO description.',
        ]);
        $package->destinations()->attach($destination);
        $package->travelStyles()->attach($style);
        $package->itineraries()->create(['day_number' => 1, 'title' => 'Arrival day', 'description' => 'Welcome itinerary copy.', 'destination_name' => 'Colombo', 'accommodation_name' => 'Test Hotel', 'meals' => 'Dinner', 'image_path' => 'packages/day.jpg']);
        $package->inclusions()->create(['item' => 'Private guide']);
        $package->exclusions()->create(['item' => 'International flights']);
        $package->faqs()->create(['question' => 'Can this be customized?', 'answer' => 'Yes, every day can change.', 'is_active' => true]);
        $package->faqs()->create(['question' => 'Hidden FAQ?', 'answer' => 'Hidden answer.', 'is_active' => false]);
        $package->reviews()->create(['customer_name' => 'Approved Guest', 'country' => 'UK', 'rating' => 5, 'review' => 'An approved review.', 'is_approved' => true]);
        $package->reviews()->create(['customer_name' => 'Unapproved Guest', 'rating' => 2, 'review' => 'Hidden review.', 'is_approved' => false]);
        $package->highlights()->create(['title' => 'Lion Rock', 'image_path' => 'packages/highlight.jpg', 'alt_text' => 'Sigiriya Lion Rock']);
        Package::factory()->for($package->category, 'category')->create(['title' => 'Related Active Package', 'is_active' => true]);
        Package::factory()->for($package->category, 'category')->create(['title' => 'Related Inactive Package', 'is_active' => false]);

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('<title>Dynamic Package SEO Title</title>', false)
            ->assertSee('Dynamic package SEO description.')
            ->assertSee('Home')
            ->assertSee('Signature Tour')
            ->assertSee('Sigiriya')
            ->assertSee('10 days · 9 nights')
            ->assertSee('Private')
            ->assertSee('Customizable')
            ->assertSee('USD 1,500.00')
            ->assertSee('Seasonal rates may apply.')
            ->assertSee('Check Availability')
            ->assertSee('Free cancellation within the stated period.')
            ->assertSee('Round-the-clock local assistance.')
            ->assertSee('Share this tour')
            ->assertSee('Dynamic overview description.')
            ->assertSee('First-time visitors')
            ->assertSee('Culture')
            ->assertSee('2–8 travellers')
            ->assertSee('Arrival day')
            ->assertSee('Welcome itinerary copy.')
            ->assertSee('Test Hotel')
            ->assertSee('Dinner')
            ->assertSee('Private guide')
            ->assertSee('International flights')
            ->assertSee('Boutique hotels and lodges.')
            ->assertSee('Private air-conditioned vehicle.')
            ->assertSee('Approved Guest')
            ->assertDontSee('Unapproved Guest')
            ->assertSee('Can this be customized?')
            ->assertDontSee('Hidden FAQ?')
            ->assertSee('Lion Rock')
            ->assertSee('Sigiriya Lion Rock')
            ->assertSee('Dynamic Package CTA')
            ->assertSee('wa.me/94771112233', false)
            ->assertSee('Related Active Package')
            ->assertDontSee('Related Inactive Package');
    }

    public function test_inactive_package_is_hidden_and_returns_not_found(): void
    {
        $package = Package::factory()->create(['title' => 'Inactive Public Package', 'is_active' => false]);

        $this->get(route('packages.index'))->assertDontSee($package->title);
        $this->get(route('packages.show', $package))->assertNotFound();
    }
}
