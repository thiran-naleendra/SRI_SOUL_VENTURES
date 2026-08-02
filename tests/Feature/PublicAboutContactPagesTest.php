<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\PageSection;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\WebsiteSetting;
use App\Notifications\NewPublicEnquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicAboutContactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_renders_dynamic_sections_active_people_testimonials_statistics_and_ctas(): void
    {
        WebsiteSetting::create(['website_name' => 'Sri Soul Test', 'whatsapp_number' => '+94 77 222 3344']);
        $this->section('about', 'hero', ['heading' => 'Dynamic About Hero', 'content' => 'Dynamic hero copy.']);
        $this->section('about', 'story', ['heading' => 'Dynamic Company Story', 'content' => 'The complete company story.']);
        $this->section('about', 'mission', ['heading' => 'Dynamic Mission', 'content' => 'Mission copy.']);
        $this->section('about', 'vision', ['heading' => 'Dynamic Vision', 'content' => 'Vision copy.']);
        $this->section('about', 'promise', ['heading' => 'Dynamic Promise', 'content' => 'Promise copy.']);
        $this->section('about', 'why_us', ['heading' => 'Dynamic Why Us', 'settings' => ['items' => [['icon' => '✓', 'title' => 'Genuine local insight', 'text' => 'Why item copy.']]]]);
        $this->section('about', 'statistics', ['settings' => ['items' => [['value' => '250+', 'label' => 'Tailored journeys']]]]);
        $this->section('about', 'team', ['heading' => 'Dynamic Team Heading']);
        $this->section('about', 'testimonials', ['heading' => 'Dynamic Testimonials Heading']);
        $this->section('about', 'custom_journey_cta', ['heading' => 'Dynamic Custom CTA', 'content' => 'Custom CTA copy.', 'button_text' => 'Plan test journey', 'button_url' => '/custom-tours']);
        $this->section('about', 'whatsapp_cta', ['heading' => 'Dynamic WhatsApp CTA', 'button_text' => 'Chat with test team']);

        TeamMember::create(['name' => 'Active Team Member', 'designation' => 'Travel Designer', 'biography' => 'Active biography.', 'is_active' => true]);
        TeamMember::create(['name' => 'Inactive Team Member', 'designation' => 'Guide', 'is_active' => false]);
        Testimonial::factory()->create(['customer_name' => 'Active About Guest', 'is_active' => true]);
        Testimonial::factory()->create(['customer_name' => 'Inactive About Guest', 'is_active' => false]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Dynamic About Hero')
            ->assertSee('Dynamic Company Story')
            ->assertSee('Dynamic Mission')
            ->assertSee('Dynamic Vision')
            ->assertSee('Dynamic Promise')
            ->assertSee('Dynamic Why Us')
            ->assertSee('Genuine local insight')
            ->assertSee('250+')
            ->assertSee('Tailored journeys')
            ->assertSee('Dynamic Team Heading')
            ->assertSee('Active Team Member')
            ->assertDontSee('Inactive Team Member')
            ->assertSee('Dynamic Testimonials Heading')
            ->assertSee('Active About Guest')
            ->assertDontSee('Inactive About Guest')
            ->assertSee('Dynamic Custom CTA')
            ->assertSee('Dynamic WhatsApp CTA')
            ->assertSee('wa.me/94772223344', false);
    }

    public function test_contact_page_uses_sections_settings_social_links_map_and_active_faqs(): void
    {
        WebsiteSetting::create([
            'website_name' => 'Sri Soul Contact Test',
            'primary_phone' => '+94 11 123 4567',
            'secondary_phone' => '+94 11 765 4321',
            'primary_email' => 'hello@srisoul.test',
            'secondary_email' => 'travel@srisoul.test',
            'whatsapp_number' => '+94 77 555 6677',
            'address' => '123 Test Road, Colombo',
            'business_hours' => 'Monday–Friday, 9–5',
            'google_maps_embed_url' => 'https://maps.google.com/maps?q=colombo&output=embed',
            'facebook_url' => 'https://facebook.com/srisoultest',
            'instagram_url' => 'https://instagram.com/srisoultest',
            'youtube_url' => 'https://youtube.com/@srisoultest',
            'linkedin_url' => 'https://linkedin.com/company/srisoultest',
        ]);
        $this->section('contact', 'hero', ['heading' => 'Dynamic Contact Hero', 'content' => 'Contact hero copy.']);
        $this->section('contact', 'contact_details', ['heading' => 'Dynamic Contact Details']);
        $this->section('contact', 'form', ['heading' => 'Dynamic Contact Form Heading']);
        $this->section('contact', 'faqs', ['heading' => 'Dynamic Contact FAQs']);
        Faq::create(['question' => 'Active contact question?', 'answer' => 'Active contact answer.', 'is_active' => true]);
        Faq::create(['question' => 'Inactive contact question?', 'answer' => 'Hidden answer.', 'is_active' => false]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Dynamic Contact Hero')
            ->assertSee('Dynamic Contact Details')
            ->assertSee('Dynamic Contact Form Heading')
            ->assertSee('Dynamic Contact FAQs')
            ->assertSee('123 Test Road, Colombo')
            ->assertSee('+94 11 123 4567')
            ->assertSee('hello@srisoul.test')
            ->assertSee('+94 77 555 6677')
            ->assertSee('Monday–Friday, 9–5')
            ->assertSee('maps.google.com', false)
            ->assertSee('facebook.com/srisoultest', false)
            ->assertSee('instagram.com/srisoultest', false)
            ->assertSee('youtube.com/@srisoultest', false)
            ->assertSee('linkedin.com/company/srisoultest', false)
            ->assertSee('Active contact question?')
            ->assertDontSee('Inactive contact question?')
            ->assertSee('action="'.route('contact.store').'"', false)
            ->assertSee('name="website"', false);
    }

    public function test_valid_contact_submission_is_stored_with_metadata_and_notifies_admin(): void
    {
        Notification::fake();
        WebsiteSetting::create(['website_name' => 'Sri Soul', 'primary_email' => 'admin@srisoul.test']);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.44', 'HTTP_USER_AGENT' => 'ContactFormTest/1.0'])
            ->post(route('contact.store'), $this->contactPayload());

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('contact_enquiries', [
            'name' => 'Saman Test',
            'email' => 'saman@example.test',
            'phone' => '+94112223344',
            'country' => 'Sri Lanka',
            'subject' => 'Planning question',
            'message' => 'Please help us plan a memorable trip.',
            'status' => 'new',
            'is_read' => false,
            'ip_address' => '203.0.113.44',
            'user_agent' => 'ContactFormTest/1.0',
        ]);
        Notification::assertSentOnDemand(NewPublicEnquiryNotification::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'admin@srisoul.test' && $notification->reference === 'Planning question');
    }

    public function test_contact_validation_preserves_input_and_honeypot_blocks_spam(): void
    {
        Notification::fake();

        $this->from(route('contact'))->post(route('contact.store'), [
            ...$this->contactPayload(),
            'email' => 'invalid-email',
            'message' => '',
        ])->assertRedirect(route('contact'))
            ->assertSessionHasErrors(['email', 'message'])
            ->assertSessionHasInput('name', 'Saman Test');

        $this->post(route('contact.store'), [...$this->contactPayload(), 'website' => 'spam.example'])
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('contact_enquiries', 0);
        Notification::assertNothingSent();
    }

    public function test_contact_submission_is_rate_limited(): void
    {
        Notification::fake();

        foreach (range(1, 5) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.88'])->post(route('contact.store'), $this->contactPayload())->assertRedirect();
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.88'])->post(route('contact.store'), $this->contactPayload())->assertTooManyRequests();
    }

    private function section(string $page, string $key, array $attributes): PageSection
    {
        return PageSection::create([
            'page_key' => $page,
            'section_key' => $key,
            'display_order' => 10,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function contactPayload(): array
    {
        return [
            'name' => 'Saman Test',
            'email' => 'Saman@Example.Test',
            'phone' => '+94112223344',
            'country' => 'Sri Lanka',
            'subject' => 'Planning question',
            'message' => 'Please help us plan a memorable trip.',
            'website' => '',
        ];
    }
}
