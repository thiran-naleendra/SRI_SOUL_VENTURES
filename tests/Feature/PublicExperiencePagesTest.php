<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\PageSection;
use App\Models\TravelStyle;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicExperiencePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filter_matches_experience_content_and_destination(): void
    {
        $match = Experience::factory()->create(['title' => 'Needle Safari']);
        Experience::factory()->create(['title' => 'Unrelated Cooking Class']);

        $this->get(route('experiences.index', ['search' => 'Needle']))
            ->assertOk()
            ->assertSee($match->title)
            ->assertDontSee('Unrelated Cooking Class');
    }

    public function test_category_filter(): void
    {
        $category = ExperienceCategory::factory()->create();
        $match = Experience::factory()->for($category, 'category')->create(['title' => 'Category Match']);
        Experience::factory()->create(['title' => 'Category Miss']);

        $this->get(route('experiences.index', ['category' => $category->id]))
            ->assertSee($match->title)
            ->assertDontSee('Category Miss');
    }

    public function test_destination_filter(): void
    {
        $destination = Destination::factory()->create();
        $match = Experience::factory()->for($destination)->create(['title' => 'Destination Match']);
        Experience::factory()->create(['title' => 'Destination Miss']);

        $this->get(route('experiences.index', ['destination' => $destination->id]))
            ->assertSee($match->title)
            ->assertDontSee('Destination Miss');
    }

    public function test_travel_style_filter_accepts_slug_and_excludes_inactive_styles(): void
    {
        $style = TravelStyle::factory()->create(['slug' => 'slow-travel', 'is_active' => true]);
        $inactiveStyle = TravelStyle::factory()->create(['slug' => 'hidden-style', 'is_active' => false]);
        $match = Experience::factory()->create(['title' => 'Style Match']);
        $match->travelStyles()->attach($style);
        $hidden = Experience::factory()->create(['title' => 'Inactive Style Match']);
        $hidden->travelStyles()->attach($inactiveStyle);

        $this->get(route('experiences.index', ['travel_style' => 'slow-travel']))
            ->assertSee($match->title)
            ->assertDontSee($hidden->title);

        $this->get(route('experiences.index', ['travel_style' => 'hidden-style']))
            ->assertDontSee($hidden->title);
    }

    public function test_every_duration_range_filter(): void
    {
        Experience::factory()->create(['title' => 'Short Experience', 'duration_value' => 2, 'duration_unit' => 'hours']);
        Experience::factory()->create(['title' => 'Half Day Experience', 'duration_value' => 6, 'duration_unit' => 'hours']);
        Experience::factory()->create(['title' => 'Full Day Experience', 'duration_value' => 1, 'duration_unit' => 'day']);
        Experience::factory()->create(['title' => 'Multi Day Experience', 'duration_value' => 3, 'duration_unit' => 'days']);

        $this->get(route('experiences.index', ['duration' => 'under_4_hours']))->assertSee('Short Experience')->assertDontSee('Half Day Experience');
        $this->get(route('experiences.index', ['duration' => 'half_day']))->assertSee('Half Day Experience')->assertDontSee('Full Day Experience');
        $this->get(route('experiences.index', ['duration' => 'full_day']))->assertSee('Full Day Experience')->assertDontSee('Multi Day Experience');
        $this->get(route('experiences.index', ['duration' => 'multi_day']))->assertSee('Multi Day Experience')->assertDontSee('Short Experience');
    }

    public function test_minimum_and_maximum_price_filters(): void
    {
        Experience::factory()->create(['title' => 'Budget Experience', 'starting_price' => 50]);
        Experience::factory()->create(['title' => 'Midrange Experience', 'starting_price' => 150]);
        Experience::factory()->create(['title' => 'Premium Experience', 'starting_price' => 350]);

        $this->get(route('experiences.index', ['price_min' => 100, 'price_max' => 200]))
            ->assertSee('Midrange Experience')
            ->assertDontSee('Budget Experience')
            ->assertDontSee('Premium Experience');
    }

    public function test_popular_and_newest_sorting(): void
    {
        Experience::factory()->create(['title' => 'Older Ordinary', 'is_popular' => false, 'display_order' => 1, 'created_at' => now()->subDays(2)]);
        Experience::factory()->create(['title' => 'Popular Choice', 'is_popular' => true, 'display_order' => 20, 'created_at' => now()->subDay()]);
        Experience::factory()->create(['title' => 'Newest Choice', 'is_popular' => false, 'display_order' => 2, 'created_at' => now()]);

        $this->get(route('experiences.index', ['sort' => 'popular']))
            ->assertSeeInOrder(['Popular Choice', 'Older Ordinary', 'Newest Choice']);
        $this->get(route('experiences.index', ['sort' => 'newest']))
            ->assertSeeInOrder(['Newest Choice', 'Popular Choice', 'Older Ordinary']);
    }

    public function test_price_sorting_in_both_directions(): void
    {
        Experience::factory()->create(['title' => 'Low Price', 'starting_price' => 25]);
        Experience::factory()->create(['title' => 'Middle Price', 'starting_price' => 125]);
        Experience::factory()->create(['title' => 'High Price', 'starting_price' => 500]);

        $this->get(route('experiences.index', ['sort' => 'price_asc']))
            ->assertSeeInOrder(['Low Price', 'Middle Price', 'High Price']);
        $this->get(route('experiences.index', ['sort' => 'price_desc']))
            ->assertSeeInOrder(['High Price', 'Middle Price', 'Low Price']);
    }

    public function test_pagination_keeps_all_query_string_filters(): void
    {
        Experience::factory()->count(13)->create(['short_description' => 'Pagination Needle']);

        $this->get(route('experiences.index', ['search' => 'Pagination Needle', 'sort' => 'newest', 'price_min' => 10]))
            ->assertOk()
            ->assertSee('search=Pagination%20Needle&amp;sort=newest&amp;price_min=10&amp;page=2', false);
    }

    public function test_details_render_all_relationships_map_ctas_related_content_and_seo(): void
    {
        WebsiteSetting::create(['website_name' => 'Sri Soul', 'whatsapp_number' => '+94 77 555 1212']);
        PageSection::create(['page_key' => 'experiences', 'section_key' => 'custom_journey_cta', 'heading' => 'Custom Journey Test CTA', 'button_text' => 'Plan this test', 'button_url' => '/custom-tours', 'display_order' => 1, 'is_active' => true]);
        PageSection::create(['page_key' => 'experiences', 'section_key' => 'whatsapp_cta', 'heading' => 'WhatsApp Test CTA', 'button_text' => 'Message the test team', 'display_order' => 2, 'is_active' => true]);

        $experience = Experience::factory()->create([
            'title' => 'Complete Detail Experience',
            'badge_text' => 'Signature',
            'location' => 'Sigiriya',
            'duration_value' => 4,
            'duration_unit' => 'hours',
            'starting_price' => 175,
            'full_description' => 'Complete full description.',
            'important_information' => 'Bring comfortable shoes.',
            'cover_image' => 'experiences/cover.jpg',
            'latitude' => 7.957,
            'longitude' => 80.760,
            'meta_title' => 'Dynamic Experience SEO Title',
            'meta_description' => 'Dynamic experience SEO description.',
        ]);
        $experience->images()->create(['image_path' => 'experiences/gallery.jpg', 'alt_text' => 'Gallery test alt', 'caption' => 'Gallery caption']);
        $experience->highlights()->create(['item' => 'A special highlight']);
        $experience->inclusions()->create(['item' => 'Expert guide included']);
        $experience->exclusions()->create(['item' => 'Lunch not included']);
        Experience::factory()->for($experience->category, 'category')->create(['title' => 'Related Active Experience', 'is_active' => true]);
        Experience::factory()->for($experience->category, 'category')->create(['title' => 'Related Inactive Experience', 'is_active' => false]);

        $this->get(route('experiences.show', $experience))
            ->assertOk()
            ->assertSee('<title>Dynamic Experience SEO Title</title>', false)
            ->assertSee('Dynamic experience SEO description.')
            ->assertSee('Complete Detail Experience')
            ->assertSee('Signature')
            ->assertSee('Sigiriya')
            ->assertSee('Complete full description.')
            ->assertSee('Gallery test alt')
            ->assertSee('Gallery caption')
            ->assertSee('A special highlight')
            ->assertSee('Expert guide included')
            ->assertSee('Lunch not included')
            ->assertSee('Bring comfortable shoes.')
            ->assertSee('google.com/maps', false)
            ->assertSee('Related Active Experience')
            ->assertDontSee('Related Inactive Experience')
            ->assertSee('Custom Journey Test CTA')
            ->assertSee('WhatsApp Test CTA')
            ->assertSee('wa.me/94775551212', false);
    }

    public function test_inactive_experience_details_return_not_found(): void
    {
        $experience = Experience::factory()->create(['is_active' => false]);

        $this->get(route('experiences.show', $experience))->assertNotFound();
    }
}
