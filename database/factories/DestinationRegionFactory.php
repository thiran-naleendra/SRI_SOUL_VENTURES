<?php

namespace Database\Factories;

use App\Models\DestinationRegion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DestinationRegion> */
class DestinationRegionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->city().' Region';

        return ['name' => $name, 'slug' => Str::slug($name), 'short_description' => fake()->sentence(), 'display_order' => 0, 'is_active' => true];
    }
}
