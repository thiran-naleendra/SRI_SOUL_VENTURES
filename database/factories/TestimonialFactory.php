<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return ['customer_name' => fake()->name(), 'country' => fake()->country(), 'testimonial' => fake()->paragraph(), 'rating' => 5, 'display_order' => 0, 'is_featured' => false, 'is_active' => true];
    }
}
