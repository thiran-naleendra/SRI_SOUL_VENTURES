<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\DestinationRegion;
use App\Models\Experience;
use App\Models\Package;
use App\Models\PageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDestinationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_searches_active_destination_content_and_region(): void
    {
        $region = DestinationRegion::factory()->create(['name' => 'Needle Region']);
        $match = Destination::factory()->for($region, 'region')->create(['name' => 'Search Match', 'best_time_to_visit' => 'December to March']);
        Destination::factory()->create(['name' => 'Search Miss']);
        Destination::factory()->create(['name' => 'Inactive Search Match', 'is_active' => false]);

        $this->get(route('destinations.index', ['search' => 'Needle Region']))
            ->assertOk()
            ->assertSee($match->name)
            ->assertDontSee('Search Miss')
            ->assertDontSee('Inactive Search Match');
    }

    public function test_region_navigation_filters_destinations_and_preserves_search(): void
    {
        $region = DestinationRegion::factory()->create(['name' => 'South Test']);
        $otherRegion = DestinationRegion::factory()->create(['name' => 'North Test']);
        $match = Destination::factory()->for($region, 'region')->create(['name' => 'Coast Match', 'short_description' => 'Ocean search term']);
        Destination::factory()->for($otherRegion, 'region')->create(['name' => 'Coast Miss', 'short_description' => 'Ocean search term']);

        $this->get(route('destinations.index', ['search' => 'Ocean', 'region' => $region->id]))
            ->assertOk()
            ->assertSee($match->name)
            ->assertDontSee('Coast Miss')
            ->assertSee('search=Ocean&amp;region='.$otherRegion->id, false);
    }

    public function test_cards_show_best_time_and_coordinate_records_appear_on_map(): void
    {
        $mapped = Destination::factory()->create([
            'name' => 'Mapped Ella',
            'best_time_to_visit' => 'January to April',
            'latitude' => 6.8667,
            'longitude' => 81.0466,
        ]);
        Destination::factory()->create(['name' => 'No Coordinate Place', 'latitude' => null, 'longitude' => null]);

        $this->get(route('destinations.index'))
            ->assertOk()
            ->assertSee('Best time to visit')
            ->assertSee('January to April')
            ->assertSee('Interactive-style map of Sri Lanka destinations')
            ->assertSee('--pin-left:', false)
            ->assertSee('title="'.$mapped->name.'"', false);
    }

    public function test_pagination_preserves_search_and_region_filters(): void
    {
        $region = DestinationRegion::factory()->create();
        Destination::factory()->count(13)->for($region, 'region')->create(['short_description' => 'Pagination Coast Needle']);

        $this->get(route('destinations.index', ['search' => 'Pagination Coast Needle', 'region' => $region->id]))
            ->assertOk()
            ->assertSee('search=Pagination%20Coast%20Needle&amp;region='.$region->id.'&amp;page=2', false);
    }

    public function test_details_render_all_content_relationships_map_cta_and_dynamic_seo(): void
    {
        PageSection::create(['page_key' => 'destinations', 'section_key' => 'custom_journey_cta', 'heading' => 'Dynamic Destination CTA', 'content' => 'Destination CTA copy.', 'button_text' => 'Build destination tour', 'button_url' => '/custom-tours', 'display_order' => 1, 'is_active' => true]);
        $destination = Destination::factory()->create([
            'name' => 'Complete Ella',
            'short_description' => 'Ella short overview.',
            'full_description' => 'Ella complete overview.',
            'cover_image' => 'destinations/ella-cover.jpg',
            'best_time_to_visit' => 'January to April',
            'latitude' => 6.8667,
            'longitude' => 81.0466,
            'meta_title' => 'Dynamic Destination SEO Title',
            'meta_description' => 'Dynamic destination SEO description.',
        ]);
        $destination->images()->create(['image_path' => 'destinations/gallery.jpg', 'alt_text' => 'Ella gallery alt', 'caption' => 'Ella gallery caption']);
        $destination->attractions()->create(['title' => 'Nine Arches Bridge', 'description' => 'A famous railway bridge.', 'image_path' => 'destinations/bridge.jpg']);
        $destination->activities()->create(['title' => 'Hike Little Adam’s Peak', 'description' => 'A rewarding morning walk.', 'icon' => '△']);
        $destination->travelTips()->create(['title' => 'Pack a light jacket', 'description' => 'Evenings can be cool.']);

        Experience::factory()->for($destination)->create(['title' => 'Active Ella Experience', 'is_active' => true]);
        Experience::factory()->for($destination)->create(['title' => 'Inactive Ella Experience', 'is_active' => false]);
        $activePackage = Package::factory()->create(['title' => 'Active Ella Package', 'is_active' => true]);
        $activePackage->destinations()->attach($destination);
        $inactivePackage = Package::factory()->create(['title' => 'Inactive Ella Package', 'is_active' => false]);
        $inactivePackage->destinations()->attach($destination);

        $this->get(route('destinations.show', $destination))
            ->assertOk()
            ->assertSee('<title>Dynamic Destination SEO Title</title>', false)
            ->assertSee('Dynamic destination SEO description.')
            ->assertSee('Complete Ella')
            ->assertSee($destination->region->name)
            ->assertSee('Ella complete overview.')
            ->assertSee('January to April')
            ->assertSee('Ella gallery alt')
            ->assertSee('Ella gallery caption')
            ->assertSee('Nine Arches Bridge')
            ->assertSee('A famous railway bridge.')
            ->assertSee('Hike Little Adam’s Peak')
            ->assertSee('A rewarding morning walk.')
            ->assertSee('Pack a light jacket')
            ->assertSee('Evenings can be cool.')
            ->assertSee('google.com/maps', false)
            ->assertSee('Active Ella Experience')
            ->assertDontSee('Inactive Ella Experience')
            ->assertSee('Active Ella Package')
            ->assertDontSee('Inactive Ella Package')
            ->assertSee('Dynamic Destination CTA')
            ->assertSee('Destination CTA copy.');
    }

    public function test_inactive_destination_is_hidden_and_returns_not_found(): void
    {
        $destination = Destination::factory()->create(['name' => 'Inactive Destination Test', 'is_active' => false]);

        $this->get(route('destinations.index'))->assertDontSee($destination->name);
        $this->get(route('destinations.show', $destination))->assertNotFound();
    }
}
