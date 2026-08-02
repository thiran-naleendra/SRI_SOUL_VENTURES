<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Package> */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return ['package_category_id' => PackageCategory::factory(), 'title' => $title, 'slug' => Str::slug($title), 'short_description' => fake()->sentence(), 'days' => 7, 'nights' => 6, 'starting_price' => fake()->randomFloat(2, 300, 5000), 'currency' => 'USD', 'minimum_travelers' => 1, 'is_featured' => false, 'is_popular' => false, 'is_customizable' => true, 'is_active' => true, 'display_order' => 0];
    }
}
