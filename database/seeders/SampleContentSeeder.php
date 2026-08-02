<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\DestinationRegion;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\TravelStyle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogTaxonomySeeder::class);

        $destinations = $this->seedDestinations();
        $this->seedExperiences($destinations);
        $this->seedPackages($destinations);
    }

    /** @return array<string, Destination> */
    private function seedDestinations(): array
    {
        $items = [
            ['Mirissa', 'South Coast', 'Palm-fringed bays, ocean adventures and easy-going coastal life.', 'November to April', 5.9483, 80.4716, true],
            ['Ella', 'Hill Country', 'A relaxed mountain town surrounded by tea gardens, waterfalls and scenic trails.', 'January to March', 6.8667, 81.0466, true],
            ['Sigiriya', 'Cultural Triangle', 'Ancient rock-fortress history, village landscapes and unforgettable sunrise views.', 'January to April', 7.9570, 80.7603, true],
            ['Yala National Park', 'Wildlife and Nature', 'Sri Lanka’s celebrated wilderness of leopards, elephants and coastal lagoons.', 'February to July', 6.3725, 81.5185, true],
            ['Galle', 'South Coast', 'A living UNESCO-listed fort filled with colonial architecture, cafés and sea views.', 'December to April', 6.0329, 80.2168, true],
            ['Kandy', 'Cultural Triangle', 'Sri Lanka’s cultural capital, set around a lake amid forested hills.', 'December to April', 7.2906, 80.6337, true],
            ['Trincomalee', 'East Coast', 'Clear water, sweeping beaches and a natural harbour on the island’s east coast.', 'May to September', 8.5874, 81.2152, false],
            ['Jaffna', 'North Sri Lanka', 'Distinctive northern culture, island temples and richly flavoured Tamil cuisine.', 'January to September', 9.6615, 80.0255, false],
            ['Nuwara Eliya', 'Hill Country', 'Cool-climate tea country with rolling estates, gardens and misty viewpoints.', 'February to May', 6.9497, 80.7891, true],
            ['Arugam Bay', 'East Coast', 'A laid-back surf town with world-class breaks and wild landscapes nearby.', 'May to September', 6.8404, 81.8368, true],
            ['Colombo', 'Colombo and Around', 'A lively gateway city where contemporary Sri Lanka meets layered colonial history.', 'January to March', 6.9271, 79.8612, false],
        ];

        $destinations = [];

        foreach ($items as $index => [$name, $regionName, $shortDescription, $bestTime, $latitude, $longitude, $featured]) {
            $slug = Str::slug($name);
            $destination = Destination::withTrashed()->firstOrNew(['slug' => $slug]);
            $destination->fill([
                'destination_region_id' => DestinationRegion::where('name', $regionName)->valueOrFail('id'),
                'name' => $name,
                'short_description' => $shortDescription,
                'full_description' => $shortDescription.' Discover the area at a thoughtful pace with local insight, time for spontaneous stops and experiences that connect you with the surrounding community and landscape.',
                'cover_image' => "placeholders/destinations/{$slug}.svg",
                'best_time_to_visit' => $bestTime,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_featured' => $featured,
                'is_active' => true,
                'display_order' => ($index + 1) * 10,
                'meta_title' => "Visit {$name} | Sri Soul Ventures",
                'meta_description' => $shortDescription,
            ]);
            $destination->save();

            if ($destination->trashed()) {
                $destination->restore();
            }

            $destination->activities()->updateOrCreate(
                ['display_order' => 10],
                ['title' => "Explore {$name} with a local", 'description' => 'Take an unhurried, locally guided introduction to the destination.', 'icon' => '✦'],
            );
            $destination->travelTips()->updateOrCreate(
                ['display_order' => 10],
                ['title' => 'Plan for an early start', 'description' => 'Morning departures offer cooler temperatures, softer light and quieter landmarks.'],
            );

            $destinations[$name] = $destination;
        }

        return $destinations;
    }

    /** @param array<string, Destination> $destinations */
    private function seedExperiences(array $destinations): void
    {
        $items = [
            ['Whale Watching in Mirissa', 'Mirissa', 'Wildlife and Nature', ['Adventure', 'Family'], 4, 'hours', 65, 'Set out at sunrise with an experienced crew to look for blue whales, dolphins and other marine life.'],
            ['Yala Safari Adventure', 'Yala National Park', 'Wildlife and Nature', ['Adventure', 'Photography', 'Family'], 5, 'hours', 75, 'Explore Yala’s varied habitats with a naturalist guide in search of leopards, elephants and colourful birdlife.'],
            ['Scenic Train Ride to Ella', 'Ella', 'Culture and Heritage', ['Backpacking', 'Photography', 'Culture and Heritage'], 7, 'hours', 35, 'Travel through cloud forest, tea country and mountain villages on Sri Lanka’s most celebrated railway journey.'],
            ['Tea Experience in Nuwara Eliya', 'Nuwara Eliya', 'Food and Local Life', ['Culture and Heritage', 'Family'], 3, 'hours', 40, 'Walk through a working tea estate, meet local experts and taste freshly produced Ceylon tea.'],
            ['Surfing in Arugam Bay', 'Arugam Bay', 'Beach Activities', ['Adventure', 'Backpacking'], 2, 'hours', 30, 'Learn the basics or improve your technique with a local instructor at a break suited to your ability.'],
            ['Cultural Dance Experience', 'Kandy', 'Culture and Heritage', ['Culture and Heritage', 'Family'], 2, 'hours', 25, 'Experience drumming, ceremonial dance and fire traditions in Sri Lanka’s historic cultural capital.'],
            ['Village Cooking Experience', 'Sigiriya', 'Food and Local Life', ['Family', 'Culture and Heritage'], 4, 'hours', 45, 'Cook fragrant curries with a village host and share a generous lunch made from seasonal local ingredients.'],
            ['Hidden Waterfalls Tour', 'Ella', 'Adventure', ['Adventure', 'Photography'], 6, 'hours', 55, 'Hike quiet hill-country paths to lesser-known cascades with a guide who knows the landscape intimately.'],
        ];

        foreach ($items as $index => [$title, $destinationName, $categoryName, $styleNames, $duration, $unit, $price, $description]) {
            $slug = Str::slug($title);
            $experience = Experience::withTrashed()->firstOrNew(['slug' => $slug]);
            $experience->fill([
                'experience_category_id' => ExperienceCategory::where('name', $categoryName)->valueOrFail('id'),
                'destination_id' => $destinations[$destinationName]->id,
                'title' => $title,
                'badge_text' => $index < 3 ? 'Guest favourite' : null,
                'short_description' => $description,
                'full_description' => $description.' Your local host will adapt the pace to the group and share practical context throughout the experience.',
                'location' => $destinationName,
                'duration_value' => $duration,
                'duration_unit' => $unit,
                'starting_price' => $price,
                'currency' => 'USD',
                'cover_image' => "placeholders/experiences/{$slug}.svg",
                'latitude' => $destinations[$destinationName]->latitude,
                'longitude' => $destinations[$destinationName]->longitude,
                'important_information' => 'Comfortable footwear, sun protection and a reusable water bottle are recommended. Final timings may vary with local conditions.',
                'is_featured' => $index < 4,
                'is_popular' => $index < 6,
                'is_active' => true,
                'display_order' => ($index + 1) * 10,
                'meta_title' => "{$title} | Sri Soul Ventures",
                'meta_description' => $description,
            ]);
            $experience->save();

            if ($experience->trashed()) {
                $experience->restore();
            }

            $experience->travelStyles()->sync(TravelStyle::whereIn('name', $styleNames)->pluck('id'));
            $experience->highlights()->updateOrCreate(['display_order' => 10], ['item' => 'A personal experience led by a knowledgeable local host']);
            $experience->highlights()->updateOrCreate(['display_order' => 20], ['item' => 'Small-group pacing with time for questions and photographs']);
            $experience->inclusions()->updateOrCreate(['display_order' => 10], ['item' => 'English-speaking local guide or host']);
            $experience->inclusions()->updateOrCreate(['display_order' => 20], ['item' => 'All activity-specific equipment and entrance fees']);
            $experience->exclusions()->updateOrCreate(['display_order' => 10], ['item' => 'Hotel transfers unless specifically arranged']);
        }
    }

    /** @param array<string, Destination> $destinations */
    private function seedPackages(array $destinations): void
    {
        $items = [
            ['Southern Escape', 'Short Escapes', 4, 3, 325, ['Galle', 'Mirissa'], ['Relax'], 'A relaxed introduction to Galle Fort and Sri Lanka’s palm-fringed south coast.'],
            ['Sri Lanka Highlights', 'Sri Lanka Highlights', 7, 6, 520, ['Sigiriya', 'Kandy', 'Nuwara Eliya', 'Ella', 'Mirissa', 'Galle', 'Colombo'], ['Culture and Heritage', 'Family'], 'A perfectly paced first journey through ancient heritage, tea country, mountain scenery and the south coast.'],
            ['Adventure Explorer', 'Adventure Tours', 9, 8, 790, ['Sigiriya', 'Ella', 'Yala National Park', 'Arugam Bay'], ['Adventure', 'Photography'], 'An active island adventure combining hikes, wildlife, surfing and memorable landscapes.'],
            ['Complete Sri Lanka', 'Complete Sri Lanka', 14, 13, 1450, ['Sigiriya', 'Kandy', 'Nuwara Eliya', 'Ella', 'Yala National Park', 'Mirissa', 'Galle', 'Colombo'], ['Culture and Heritage', 'Relax'], 'A comprehensive two-week journey from the Cultural Triangle to tea country, wildlife and the coast.'],
            ['Romantic Getaway', 'Honeymoon Packages', 8, 7, 980, ['Kandy', 'Ella', 'Mirissa', 'Galle'], ['Honeymoon', 'Luxury', 'Relax'], 'Intimate stays, scenic journeys and private experiences created for two.'],
            ['Family Adventure', 'Family Packages', 10, 9, 1050, ['Sigiriya', 'Kandy', 'Nuwara Eliya', 'Yala National Park', 'Mirissa'], ['Family', 'Adventure'], 'A family-friendly mix of wildlife, culture, easy adventure and beach time.'],
        ];

        foreach ($items as $index => [$title, $categoryName, $days, $nights, $price, $destinationNames, $styleNames, $description]) {
            $slug = Str::slug($title);
            $package = Package::withTrashed()->firstOrNew(['slug' => $slug]);
            $package->fill([
                'package_category_id' => PackageCategory::where('name', $categoryName)->valueOrFail('id'),
                'title' => $title,
                'badge_text' => $title === 'Sri Lanka Highlights' ? 'Best seller' : ($index < 3 ? 'Popular journey' : null),
                'short_description' => $description,
                'full_description' => $description.' Travel in a private air-conditioned vehicle with a friendly English-speaking chauffeur-guide, thoughtfully selected stays and the freedom to personalize the journey.',
                'cover_image' => "placeholders/packages/{$slug}.svg",
                'days' => $days,
                'nights' => $nights,
                'starting_price' => $price,
                'currency' => 'USD',
                'price_note' => 'From price per person based on two travellers sharing. Seasonal supplements may apply.',
                'minimum_travelers' => 2,
                'maximum_travelers' => $title === 'Family Adventure' ? 8 : 6,
                'tour_type' => 'Private guided tour',
                'physical_level' => $title === 'Adventure Explorer' ? 'Active' : 'Easy to moderate',
                'perfect_for' => $this->perfectFor($title),
                'accommodation_summary' => 'Handpicked boutique hotels and characterful guesthouses in convenient locations, including daily breakfast.',
                'transportation_summary' => 'Private air-conditioned vehicle with an English-speaking chauffeur-guide throughout the tour.',
                'cancellation_policy' => 'Cancellation terms vary by season and hotel. The final quotation clearly states deposit and cancellation deadlines before confirmation.',
                'support_text' => 'Local assistance is available throughout your journey.',
                'terms_and_conditions' => 'The final itinerary, availability, inclusions and payment schedule are confirmed in your personalized quotation.',
                'is_featured' => in_array($title, ['Sri Lanka Highlights', 'Romantic Getaway', 'Complete Sri Lanka'], true),
                'is_popular' => $index < 4,
                'is_customizable' => true,
                'is_active' => true,
                'display_order' => ($index + 1) * 10,
                'meta_title' => "{$title} Tour | Sri Soul Ventures",
                'meta_description' => $description,
            ]);
            $package->save();

            if ($package->trashed()) {
                $package->restore();
            }

            $package->destinations()->sync(collect($destinationNames)->map(fn (string $name) => $destinations[$name]->id));
            $package->travelStyles()->sync(TravelStyle::whereIn('name', $styleNames)->pluck('id'));

            if ($title === 'Sri Lanka Highlights') {
                $this->seedSriLankaHighlights($package);
            }
        }
    }

    private function seedSriLankaHighlights(Package $package): void
    {
        $days = [
            [1, 'Welcome to Sri Lanka', 'Meet your chauffeur-guide at the airport and settle into Colombo. Take an optional evening walk along Galle Face Green.', 'Colombo', 'Colombo city hotel', 'Dinner'],
            [2, 'Colombo to Sigiriya', 'Travel into the Cultural Triangle, stopping for a relaxed village lunch before an afternoon climb of Sigiriya Rock Fortress.', 'Sigiriya', 'Cultural Triangle boutique hotel', 'Breakfast, lunch'],
            [3, 'Temples and traditions in Kandy', 'Visit the Dambulla cave temples before continuing to Kandy for a guided walk and an evening cultural performance.', 'Kandy', 'Kandy hillside hotel', 'Breakfast'],
            [4, 'Through tea country', 'Follow winding mountain roads to Nuwara Eliya, visit a working tea estate and learn how Ceylon tea is made.', 'Nuwara Eliya', 'Tea-country bungalow', 'Breakfast'],
            [5, 'Scenic train to Ella', 'Board the celebrated hill-country train for Ella. Spend the late afternoon exploring the village or walking to a viewpoint.', 'Ella', 'Ella mountain retreat', 'Breakfast'],
            [6, 'From the hills to Mirissa', 'Descend through changing landscapes to the south coast, with time to pause at waterfalls and local roadside markets.', 'Mirissa', 'Mirissa beach hotel', 'Breakfast'],
            [7, 'Galle Fort and departure', 'Explore Galle Fort with a local guide before returning to Colombo or the airport for your onward journey.', 'Galle and Colombo', null, 'Breakfast'],
        ];

        foreach ($days as [$number, $title, $description, $destination, $hotel, $meals]) {
            $package->itineraries()->updateOrCreate(
                ['day_number' => $number],
                ['title' => $title, 'description' => $description, 'destination_name' => $destination, 'accommodation_name' => $hotel, 'meals' => $meals, 'image_path' => "placeholders/packages/sri-lanka-highlights-day-{$number}.svg", 'display_order' => $number * 10],
            );
        }

        $this->seedOrderedItems($package, 'inclusions', [
            'Six nights in handpicked accommodation with daily breakfast',
            'Private air-conditioned vehicle and English-speaking chauffeur-guide',
            'Airport arrival transfer and departure transfer',
            'Entrance fees for Sigiriya Rock Fortress and Dambulla cave temples',
            'Tea-estate visit and reserved hill-country train tickets',
            'Kandy cultural performance and guided Galle Fort walk',
            'Local support throughout the journey',
        ]);
        $this->seedOrderedItems($package, 'exclusions', [
            'International flights and Sri Lanka visa fees',
            'Lunches and dinners unless listed in the itinerary',
            'Travel insurance and personal expenses',
            'Optional activities, tips and camera permits',
        ]);

        foreach ([
            ['Climb the ancient Sigiriya Rock Fortress', 'Sigiriya Lion Rock at sunrise'],
            ['Ride the scenic train through Sri Lanka’s tea country', 'Train winding through green tea hills'],
            ['Walk the atmospheric lanes of UNESCO-listed Galle Fort', 'Historic Galle Fort by the sea'],
        ] as $index => [$title, $alt]) {
            $package->highlights()->updateOrCreate(
                ['display_order' => ($index + 1) * 10],
                ['title' => $title, 'alt_text' => $alt, 'image_path' => 'placeholders/packages/sri-lanka-highlights-highlight-'.($index + 1).'.svg'],
            );
        }

        foreach ([
            ['Can the itinerary be customized?', 'Yes. We can adjust hotels, pacing, activities and destinations around your interests and flight times.'],
            ['Is the hill-country train ticket guaranteed?', 'We request reserved seats as soon as the booking is confirmed. Availability is limited, so an equivalent scenic road journey may occasionally be offered.'],
            ['What is the best time for this journey?', 'The route works year-round. January to April generally offers the most settled conditions across the locations included.'],
            ['Is this tour suitable for families?', 'Yes. We can recommend family rooms, add rest time and adapt the Sigiriya climb and other activities for children.'],
        ] as $index => [$question, $answer]) {
            $package->faqs()->updateOrCreate(
                ['display_order' => ($index + 1) * 10],
                ['question' => $question, 'answer' => $answer, 'is_active' => true],
            );
        }

        foreach ([
            ['Maya Thompson', 'United Kingdom', 5, 'A beautifully paced introduction to Sri Lanka. Our guide made every transfer interesting, and the train to Ella was unforgettable.'],
            ['Daniel Weber', 'Germany', 5, 'The itinerary balanced famous landmarks with quiet local moments. Hotels and communication were excellent throughout.'],
            ['Sofia Martinez', 'Spain', 4, 'We loved the variety—from Sigiriya and tea country to Mirissa and Galle. The team adjusted the days thoughtfully for us.'],
        ] as $index => [$name, $country, $rating, $review]) {
            $package->reviews()->updateOrCreate(
                ['display_order' => ($index + 1) * 10],
                ['customer_name' => $name, 'country' => $country, 'rating' => $rating, 'review' => $review, 'customer_image' => null, 'is_approved' => true],
            );
        }
    }

    private function seedOrderedItems(Package $package, string $relationship, array $items): void
    {
        foreach ($items as $index => $item) {
            $package->{$relationship}()->updateOrCreate(['display_order' => ($index + 1) * 10], ['item' => $item]);
        }
    }

    private function perfectFor(string $title): string
    {
        return match ($title) {
            'Southern Escape' => 'Short stays, beach lovers and relaxed long weekends',
            'Sri Lanka Highlights' => 'First-time visitors, couples and culture-loving families',
            'Adventure Explorer' => 'Active travellers, wildlife enthusiasts and photographers',
            'Complete Sri Lanka' => 'Travellers who want an in-depth island journey',
            'Romantic Getaway' => 'Honeymoons, anniversaries and special celebrations',
            default => 'Families seeking culture, wildlife and shared adventures',
        };
    }
}
