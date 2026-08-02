<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\DestinationRegion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Destination> */
class DestinationFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return ['destination_region_id' => DestinationRegion::factory(), 'name' => $name, 'slug' => Str::slug($name), 'short_description' => fake()->sentence(), 'is_featured' => false, 'is_active' => true, 'display_order' => 0];
    }
}
