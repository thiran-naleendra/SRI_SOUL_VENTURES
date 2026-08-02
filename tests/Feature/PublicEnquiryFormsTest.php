<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TravelStyle;
use App\Models\WebsiteSetting;
use App\Notifications\NewPublicEnquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicEnquiryFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_availability_form_is_rendered_with_the_slug_submission_route(): void
    {
        $package = Package::factory()->create();

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('Check availability')
            ->assertSee('action="'.route('packages.availability.store', $package).'"', false)
            ->assertSee('name="website"', false);
    }

    public function test_valid_package_availability_is_stored_with_request_metadata_and_notifies_admin(): void
    {
        Notification::fake();
        WebsiteSetting::create(['website_name' => 'Sri Soul', 'primary_email' => 'admin@srisoul.test']);
        $package = Package::factory()->create();

        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20', 'HTTP_USER_AGENT' => 'SriSoulTestBrowser/1.0'])
            ->post(route('packages.availability.store', $package), $this->availabilityPayload());

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('package_enquiries', [
            'package_id' => $package->id,
            'customer_name' => 'Amara Silva',
            'email' => 'amara@example.test',
            'ip_address' => '203.0.113.20',
            'user_agent' => 'SriSoulTestBrowser/1.0',
            'status' => 'new',
        ]);
        Notification::assertSentOnDemand(NewPublicEnquiryNotification::class, function ($notification, $channels, $notifiable) use ($package) {
            return in_array('mail', $channels)
                && $notifiable->routes['mail'] === 'admin@srisoul.test'
                && $notification->reference === $package->title;
        });
    }

    public function test_package_availability_validation_preserves_input_and_honeypot_rejects_spam(): void
    {
        Notification::fake();
        $package = Package::factory()->create();

        $this->from(route('packages.show', $package))->post(route('packages.availability.store', $package), [
            ...$this->availabilityPayload(),
            'email' => 'not-an-email',
        ])->assertRedirect(route('packages.show', $package))
            ->assertSessionHasErrors('email')
            ->assertSessionHasInput('customer_name', 'Amara Silva');

        $this->post(route('packages.availability.store', $package), [
            ...$this->availabilityPayload(),
            'website' => 'spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('package_enquiries', 0);
        Notification::assertNothingSent();
    }

    public function test_package_availability_is_rate_limited_and_inactive_packages_are_rejected(): void
    {
        Notification::fake();
        $package = Package::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.90'])
                ->post(route('packages.availability.store', $package), $this->availabilityPayload())
                ->assertRedirect();
        }
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.90'])
            ->post(route('packages.availability.store', $package), $this->availabilityPayload())
            ->assertTooManyRequests();

        $inactive = Package::factory()->create(['is_active' => false]);
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.91'])
            ->post(route('packages.availability.store', $inactive), $this->availabilityPayload())
            ->assertNotFound();
    }

    public function test_custom_tour_page_preselects_active_package_from_slug(): void
    {
        $package = Package::factory()->create(['slug' => 'sri-lanka-highlights', 'title' => 'Sri Lanka Highlights']);
        $inactive = Package::factory()->create(['slug' => 'inactive-package', 'is_active' => false]);

        $this->get('/custom-tours?package=sri-lanka-highlights')
            ->assertOk()
            ->assertSee('<option value="'.$package->id.'" selected>Sri Lanka Highlights</option>', false)
            ->assertDontSee($inactive->title)
            ->assertSee('action="'.route('custom-tours.store').'"', false);
    }

    public function test_valid_custom_tour_request_stores_all_fields_pivots_metadata_and_notification(): void
    {
        Notification::fake();
        WebsiteSetting::create(['website_name' => 'Sri Soul', 'primary_email' => 'tours@srisoul.test']);
        $package = Package::factory()->create();
        $destination = Destination::factory()->create();
        $style = TravelStyle::factory()->create();

        $payload = $this->customTourPayload($package, $destination, $style);
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.30', 'HTTP_USER_AGENT' => 'CustomTourTest/2.0'])
            ->post(route('custom-tours.store'), $payload);

        $response->assertRedirect(route('custom-tours.success'));
        $this->assertDatabaseHas('custom_tour_requests', [
            'package_id' => $package->id,
            'customer_name' => 'Nimal Perera',
            'email' => 'nimal@example.test',
            'whatsapp_number' => '+94770000000',
            'budget_min' => 1500,
            'budget_max' => 3000,
            'currency' => 'USD',
            'ip_address' => '203.0.113.30',
            'user_agent' => 'CustomTourTest/2.0',
            'status' => 'new',
        ]);
        $tourRequestId = (int) \DB::table('custom_tour_requests')->value('id');
        $this->assertDatabaseHas('custom_tour_request_destination', ['custom_tour_request_id' => $tourRequestId, 'destination_id' => $destination->id]);
        $this->assertDatabaseHas('custom_tour_request_travel_style', ['custom_tour_request_id' => $tourRequestId, 'travel_style_id' => $style->id]);
        Notification::assertSentOnDemand(NewPublicEnquiryNotification::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'tours@srisoul.test' && $notification->reference === $package->title);

        $this->get(route('custom-tours.success'))->assertOk()->assertSee('Your journey starts here');
    }

    public function test_custom_tour_validation_honeypot_and_active_relationship_rules(): void
    {
        Notification::fake();
        $package = Package::factory()->create(['is_active' => false]);
        $destination = Destination::factory()->create(['is_active' => false]);
        $style = TravelStyle::factory()->create(['is_active' => false]);

        $this->from(route('custom-tours'))->post(route('custom-tours.store'), [
            ...$this->customTourPayload($package, $destination, $style),
            'email' => 'invalid',
            'website' => 'spam.example',
            'budget_max' => 100,
        ])->assertRedirect(route('custom-tours'))
            ->assertSessionHasErrors(['package_id', 'email', 'destination_ids.0', 'travel_style_ids.0', 'budget_max', 'website'])
            ->assertSessionHasInput('name', 'Nimal Perera');

        $this->assertDatabaseCount('custom_tour_requests', 0);
        Notification::assertNothingSent();
    }

    public function test_custom_tour_submission_is_rate_limited(): void
    {
        Notification::fake();
        $payload = $this->customTourPayload();

        foreach (range(1, 5) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.70'])->post(route('custom-tours.store'), $payload)->assertRedirect(route('custom-tours.success'));
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.70'])->post(route('custom-tours.store'), $payload)->assertTooManyRequests();
    }

    private function availabilityPayload(): array
    {
        return [
            'customer_name' => 'Amara Silva',
            'email' => 'Amara@Example.Test',
            'phone' => '+94112223344',
            'whatsapp_number' => '+94771234567',
            'country' => 'Sri Lanka',
            'preferred_start_date' => now()->addMonth()->toDateString(),
            'preferred_end_date' => now()->addMonth()->addDays(7)->toDateString(),
            'adults' => 2,
            'children' => 1,
            'message' => 'Please confirm availability.',
            'website' => '',
        ];
    }

    private function customTourPayload(?Package $package = null, ?Destination $destination = null, ?TravelStyle $style = null): array
    {
        return [
            'package_id' => $package?->id,
            'name' => 'Nimal Perera',
            'email' => 'Nimal@Example.Test',
            'phone' => '+94110000000',
            'whatsapp' => '+94770000000',
            'country' => 'Australia',
            'arrival_date' => now()->addMonths(2)->toDateString(),
            'departure_date' => now()->addMonths(2)->addDays(12)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'destination_ids' => $destination ? [$destination->id] : [],
            'travel_style_ids' => $style ? [$style->id] : [],
            'budget_min' => 1500,
            'budget_max' => 3000,
            'currency' => 'usd',
            'accommodation_preference' => 'Boutique hotels',
            'transport_preference' => 'Private vehicle',
            'special_requirements' => 'Vegetarian meals',
            'message' => 'We enjoy culture and wildlife.',
            'website' => '',
        ];
    }
}
