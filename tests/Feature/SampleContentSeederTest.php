<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use Database\Seeders\SampleContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_content_seeder_creates_complete_idempotent_catalog_data(): void
    {
        $this->seed(SampleContentSeeder::class);
        $this->seed(SampleContentSeeder::class);

        $this->assertSame(11, Destination::count());
        $this->assertSame(8, Experience::count());
        $this->assertSame(6, Package::count());

        $highlights = Package::where('slug', 'sri-lanka-highlights')->firstOrFail();

        $this->assertSame(7, $highlights->days);
        $this->assertSame(6, $highlights->nights);
        $this->assertSame('520.00', $highlights->starting_price);
        $this->assertSame('USD', $highlights->currency);
        $this->assertSame(7, $highlights->destinations()->count());
        $this->assertSame(7, $highlights->itineraries()->count());
        $this->assertSame(7, $highlights->inclusions()->count());
        $this->assertSame(4, $highlights->exclusions()->count());
        $this->assertSame(3, $highlights->highlights()->count());
        $this->assertSame(4, $highlights->faqs()->where('is_active', true)->count());
        $this->assertSame(3, $highlights->reviews()->where('is_approved', true)->count());
        $this->assertEqualsCanonicalizing(
            ['Sigiriya', 'Kandy', 'Nuwara Eliya', 'Ella', 'Mirissa', 'Galle', 'Colombo'],
            $highlights->destinations()->pluck('name')->all(),
        );
    }
}
