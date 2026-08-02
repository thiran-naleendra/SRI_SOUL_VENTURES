<?php

namespace Database\Factories;

use App\Models\TravelStyle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TravelStyle> */
class TravelStyleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['name' => Str::title($name), 'slug' => Str::slug($name), 'description' => fake()->sentence(), 'display_order' => 0, 'is_active' => true];
    }
}
