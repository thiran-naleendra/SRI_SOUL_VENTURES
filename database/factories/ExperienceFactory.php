<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Experience> */
class ExperienceFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return ['experience_category_id' => ExperienceCategory::factory(), 'destination_id' => Destination::factory(), 'title' => $title, 'slug' => Str::slug($title), 'short_description' => fake()->sentence(), 'starting_price' => fake()->randomFloat(2, 25, 1000), 'currency' => 'USD', 'is_featured' => false, 'is_popular' => false, 'is_active' => true, 'display_order' => 0];
    }
}
