<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMediaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);
    }

    public function test_it_serves_a_file_from_the_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('destinations/covers/mirissa.jpg', 'image-content');

        $response = $this->get('/uploads/destinations/covers/mirissa.jpg');

        $response->assertOk()
            ->assertHeader('Cache-Control', 'max-age=86400, public')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame('image-content', $response->streamedContent());
    }

    public function test_it_returns_not_found_for_a_missing_public_file(): void
    {
        Storage::fake('public');

        $this->get('/uploads/destinations/covers/missing.jpg')->assertNotFound();
    }
}
