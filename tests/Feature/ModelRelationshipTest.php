<?php

namespace Tests\Feature;

use App\Models\CustomTourRequest;
use App\Models\Destination;
use App\Models\DestinationActivity;
use App\Models\DestinationAttraction;
use App\Models\DestinationImage;
use App\Models\DestinationTravelTip;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\ExperienceExclusion;
use App\Models\ExperienceHighlight;
use App\Models\ExperienceImage;
use App\Models\ExperienceInclusion;
use App\Models\Package;
use App\Models\PackageEnquiry;
use App\Models\PackageExclusion;
use App\Models\PackageFaq;
use App\Models\PackageHighlight;
use App\Models\PackageImage;
use App\Models\PackageInclusion;
use App\Models\PackageItinerary;
use App\Models\PackageReview;
use App\Models\TravelStyle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_destination_relationships_and_ordering_work(): void
    {
        $destination = Destination::factory()->create();
        DestinationImage::create(['destination_id' => $destination->id, 'image_path' => 'second.jpg', 'display_order' => 20]);
        DestinationImage::create(['destination_id' => $destination->id, 'image_path' => 'first.jpg', 'display_order' => 10]);
        DestinationAttraction::create(['destination_id' => $destination->id, 'title' => 'Attraction']);
        DestinationActivity::create(['destination_id' => $destination->id, 'title' => 'Activity']);
        DestinationTravelTip::create(['destination_id' => $destination->id, 'title' => 'Tip']);

        $this->assertTrue($destination->region->is($destination->region));
        $this->assertSame(['first.jpg', 'second.jpg'], $destination->images->pluck('image_path')->all());
        $this->assertCount(1, $destination->attractions);
        $this->assertCount(1, $destination->activities);
        $this->assertCount(1, $destination->travelTips);
    }

    public function test_experience_relationships_and_ordered_children_work(): void
    {
        $experience = Experience::factory()->create();
        $style = TravelStyle::factory()->create();
        $experience->travelStyles()->attach($style);
        ExperienceImage::create(['experience_id' => $experience->id, 'image_path' => 'image.jpg']);
        ExperienceHighlight::create(['experience_id' => $experience->id, 'item' => 'Highlight']);
        ExperienceInclusion::create(['experience_id' => $experience->id, 'item' => 'Included']);
        ExperienceExclusion::create(['experience_id' => $experience->id, 'item' => 'Excluded']);

        $this->assertInstanceOf(ExperienceCategory::class, $experience->category);
        $this->assertInstanceOf(Destination::class, $experience->destination);
        $this->assertTrue($experience->travelStyles->contains($style));
        $this->assertCount(1, $experience->images);
        $this->assertCount(1, $experience->highlights);
        $this->assertCount(1, $experience->inclusions);
        $this->assertCount(1, $experience->exclusions);
    }

    public function test_package_relationships_and_ordered_children_work(): void
    {
        $package = Package::factory()->create();
        $destination = Destination::factory()->create();
        $style = TravelStyle::factory()->create();
        $package->destinations()->attach($destination);
        $package->travelStyles()->attach($style);
        PackageImage::create(['package_id' => $package->id, 'image_path' => 'package.jpg']);
        PackageItinerary::create(['package_id' => $package->id, 'day_number' => 1, 'title' => 'Arrival']);
        PackageHighlight::create(['package_id' => $package->id, 'title' => 'Highlight']);
        PackageInclusion::create(['package_id' => $package->id, 'item' => 'Included']);
        PackageExclusion::create(['package_id' => $package->id, 'item' => 'Excluded']);
        PackageFaq::create(['package_id' => $package->id, 'question' => 'Question?', 'answer' => 'Answer.']);
        PackageReview::create(['package_id' => $package->id, 'customer_name' => 'Guest', 'rating' => 5, 'review' => 'Excellent']);
        PackageEnquiry::factory()->for($package)->create();

        $this->assertTrue($package->destinations->contains($destination));
        $this->assertTrue($package->travelStyles->contains($style));
        $this->assertCount(1, $package->images);
        $this->assertCount(1, $package->itineraries);
        $this->assertCount(1, $package->highlights);
        $this->assertCount(1, $package->inclusions);
        $this->assertCount(1, $package->exclusions);
        $this->assertCount(1, $package->faqs);
        $this->assertCount(1, $package->reviews);
        $this->assertCount(1, $package->enquiries);
    }

    public function test_custom_tour_request_relationships_work(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->create();
        $destination = Destination::factory()->create();
        $style = TravelStyle::factory()->create();
        $request = CustomTourRequest::factory()->create(['assigned_user_id' => $user->id, 'package_id' => $package->id]);
        $request->destinations()->attach($destination);
        $request->travelStyles()->attach($style);

        $this->assertTrue($request->assignedUser->is($user));
        $this->assertTrue($request->package->is($package));
        $this->assertTrue($request->destinations->contains($destination));
        $this->assertTrue($request->travelStyles->contains($style));
        $this->assertTrue($user->assignedCustomTourRequests->contains($request));
    }
}
