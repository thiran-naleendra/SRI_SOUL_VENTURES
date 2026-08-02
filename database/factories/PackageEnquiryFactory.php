<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PackageEnquiry> */
class PackageEnquiryFactory extends Factory
{
    public function definition(): array
    {
        return ['package_id' => Package::factory(), 'customer_name' => fake()->name(), 'email' => fake()->safeEmail(), 'phone' => fake()->phoneNumber(), 'country' => fake()->country(), 'adults' => 2, 'children' => 0, 'message' => fake()->sentence(), 'status' => 'new'];
    }
}
