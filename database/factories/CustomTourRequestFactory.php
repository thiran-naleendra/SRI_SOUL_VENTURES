<?php

namespace Database\Factories;

use App\Models\CustomTourRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CustomTourRequest> */
class CustomTourRequestFactory extends Factory
{
    public function definition(): array
    {
        return ['customer_name' => fake()->name(), 'email' => fake()->safeEmail(), 'phone' => fake()->phoneNumber(), 'country' => fake()->country(), 'arrival_date' => now()->addMonth(), 'departure_date' => now()->addMonth()->addWeek(), 'adults' => 2, 'children' => 0, 'budget_min' => 1000, 'budget_max' => 3000, 'currency' => 'USD', 'message' => fake()->sentence(), 'status' => 'new'];
    }
}
