<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\DestinationImage;
use App\Models\DestinationRegion;
use App\Models\PageSection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_domain_tables_and_important_columns_exist(): void
    {
        $tables = [
            'website_settings', 'destination_regions', 'destinations', 'destination_images',
            'destination_attractions', 'destination_activities', 'destination_travel_tips',
            'experience_categories', 'travel_styles', 'experiences', 'experience_images',
            'experience_highlights', 'experience_inclusions', 'experience_exclusions',
            'experience_travel_style', 'package_categories', 'packages', 'package_destination',
            'package_travel_style', 'package_images', 'package_itineraries', 'package_highlights',
            'package_inclusions', 'package_exclusions', 'package_faqs', 'package_reviews',
            'package_enquiries', 'custom_tour_requests', 'custom_tour_request_destination',
            'custom_tour_request_travel_style', 'contact_enquiries', 'testimonials',
            'team_members', 'faqs', 'page_sections',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        $this->assertTrue(Schema::hasColumns('destinations', ['destination_region_id', 'slug', 'is_featured', 'is_active', 'deleted_at']));
        $this->assertTrue(Schema::hasColumns('experiences', ['starting_price', 'currency', 'is_popular', 'deleted_at']));
        $this->assertTrue(Schema::hasColumns('packages', ['discount_price', 'is_customizable', 'itinerary_pdf', 'deleted_at']));
        $this->assertTrue(Schema::hasColumns('custom_tour_requests', ['assigned_user_id', 'quotation_sent_at', 'confirmed_at']));
        $this->assertTrue(Schema::hasColumns('page_sections', ['page_key', 'section_key', 'settings']));
    }

    public function test_child_records_cascade_when_their_parent_is_force_deleted(): void
    {
        $destination = Destination::factory()->create();
        DestinationImage::create(['destination_id' => $destination->id, 'image_path' => 'destinations/test.jpg']);

        $destination->forceDelete();

        $this->assertDatabaseMissing('destination_images', ['destination_id' => $destination->id]);
    }

    public function test_major_parent_records_are_not_cascade_deleted(): void
    {
        $region = DestinationRegion::factory()->create();
        Destination::factory()->for($region, 'region')->create();

        $this->expectException(QueryException::class);

        $region->forceDelete();
    }

    public function test_json_and_boolean_values_are_cast(): void
    {
        $section = PageSection::create([
            'page_key' => 'home',
            'section_key' => 'hero',
            'settings' => ['overlay' => true],
            'is_active' => 1,
        ]);

        $this->assertSame(['overlay' => true], $section->fresh()->settings);
        $this->assertTrue($section->fresh()->is_active);
    }
}
