<?php

namespace Database\Seeders;

use App\Models\PageSection;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        $whatsappNumber = preg_replace('/\D+/', '', WebsiteSetting::value('whatsapp_number') ?? '');
        $whatsappUrl = $whatsappNumber ? 'https://wa.me/'.$whatsappNumber : null;

        foreach (['experiences', 'packages', 'destinations', 'custom_tours', 'about', 'contact'] as $page) {
            PageSection::firstOrCreate(
                ['page_key' => $page, 'section_key' => 'hero'],
                ['heading' => str($page)->replace('_', ' ')->title(), 'display_order' => 10, 'is_active' => true],
            );
        }

        $homeSections = [
            'hero' => [
                'heading' => 'Discover the soul of Sri Lanka',
                'content' => 'Private journeys, authentic experiences and thoughtful local guidance for a Sri Lanka story that feels entirely your own.',
                'button_text' => 'Plan My Journey',
                'button_url' => '/custom-tours',
                'settings' => ['highlighted_text' => 'soul of Sri Lanka', 'secondary_button_text' => 'Explore Experiences', 'secondary_button_url' => '/experiences'],
                'display_order' => 10,
            ],
            'travel_vibes' => ['heading' => 'How do you want to feel?', 'subheading' => 'Find your travel vibe', 'display_order' => 20],
            'popular_experiences' => ['heading' => 'Popular experiences', 'subheading' => 'Unforgettable moments', 'content' => 'Connect with the landscapes, traditions and people that make Sri Lanka extraordinary.', 'display_order' => 30],
            'popular_packages' => ['heading' => 'Popular Sri Lanka packages', 'subheading' => 'Handpicked journeys', 'content' => 'Thoughtfully paced itineraries shaped by local knowledge.', 'display_order' => 40],
            'popular_destinations' => ['heading' => 'Popular destinations', 'subheading' => 'Places worth knowing', 'content' => 'Find your way from ancient cities and misty hills to wild coastlines.', 'display_order' => 50],
            'why_us' => [
                'heading' => 'Why travel with us',
                'subheading' => 'Travel differently',
                'content' => 'Personal journeys, genuine connections and thoughtful support from start to finish.',
                'settings' => ['items' => [
                    ['icon' => '✦', 'title' => 'Local expertise', 'text' => 'Travel deeper with people who know and love Sri Lanka.'],
                    ['icon' => '◇', 'title' => 'Made for you', 'text' => 'Every itinerary is shaped around your interests and pace.'],
                    ['icon' => '♡', 'title' => 'Travel with care', 'text' => 'Meaningful experiences that respect local places and communities.'],
                    ['icon' => '✓', 'title' => 'Here when needed', 'text' => 'Friendly local support throughout your Sri Lankan journey.'],
                ]],
                'display_order' => 60,
            ],
            'statistics' => [
                'heading' => 'Sri Soul Ventures statistics',
                'settings' => ['items' => [
                    ['value' => '10+', 'label' => 'Years of local expertise'],
                    ['value' => '1,500+', 'label' => 'Happy travellers'],
                    ['value' => '100+', 'label' => 'Unique experiences'],
                    ['value' => '24/7', 'label' => 'Local support'],
                ]],
                'display_order' => 70,
            ],
            'testimonials' => ['heading' => 'What our guests say', 'subheading' => 'Traveller stories', 'display_order' => 80],
            'custom_journey_cta' => ['heading' => 'Your Sri Lanka story starts here', 'content' => 'Tell us what inspires you and we will shape a journey around it.', 'button_text' => 'Plan my journey', 'button_url' => '/custom-tours', 'display_order' => 90],
            'whatsapp_cta' => ['heading' => 'Prefer to chat?', 'subheading' => 'WhatsApp us', 'content' => 'Talk directly with a local travel specialist.', 'button_text' => 'Start a conversation', 'button_url' => $whatsappUrl, 'display_order' => 100],
        ];

        foreach ($homeSections as $sectionKey => $attributes) {
            $section = PageSection::firstOrCreate(
                ['page_key' => 'home', 'section_key' => $sectionKey],
                [...$attributes, 'is_active' => true],
            );

            if ($sectionKey === 'whatsapp_cta' && ! $section->button_url && $whatsappUrl) {
                $section->update(['button_url' => $whatsappUrl]);
            }
        }

        $aboutSections = [
            'story' => ['heading' => 'Travel with heart and local perspective', 'subheading' => 'Our story', 'content' => 'Sri Soul Ventures was created to share a more personal, thoughtful side of Sri Lanka.', 'display_order' => 20],
            'mission' => ['heading' => 'Meaningful journeys', 'content' => 'Create personal journeys that connect travellers with Sri Lanka’s places, people and traditions.', 'display_order' => 30],
            'vision' => ['heading' => 'Travel that gives back', 'content' => 'Help tourism become a positive force for travellers, communities and the island we call home.', 'display_order' => 40],
            'promise' => ['heading' => 'Care in every detail', 'content' => 'Offer honest advice, warm local support and experiences designed around each guest.', 'display_order' => 50],
            'why_us' => ['heading' => 'Why travel with us', 'subheading' => 'Travel differently', 'settings' => ['items' => [
                ['icon' => '✦', 'title' => 'Local insight', 'text' => 'Knowledge shaped by living and travelling throughout Sri Lanka.'],
                ['icon' => '◇', 'title' => 'Personal design', 'text' => 'Journeys built around your interests, rhythm and priorities.'],
                ['icon' => '♡', 'title' => 'Responsible choices', 'text' => 'Experiences that value communities, culture and nature.'],
                ['icon' => '✓', 'title' => 'Trusted support', 'text' => 'A friendly local team before, during and after your trip.'],
            ]], 'display_order' => 60],
            'statistics' => ['heading' => 'Our journey so far', 'settings' => ['items' => [
                ['value' => '10+', 'label' => 'Years of local expertise'],
                ['value' => '1,500+', 'label' => 'Happy travellers'],
                ['value' => '100+', 'label' => 'Unique experiences'],
                ['value' => '24/7', 'label' => 'Local support'],
            ]], 'display_order' => 70],
            'team' => ['heading' => 'Your local travel specialists', 'subheading' => 'Meet the team', 'display_order' => 80],
            'testimonials' => ['heading' => 'What our guests say', 'subheading' => 'Traveller stories', 'display_order' => 90],
            'custom_journey_cta' => ['heading' => 'Let us show you our Sri Lanka', 'content' => 'Share what inspires you and we will create a journey that feels entirely your own.', 'button_text' => 'Plan a custom tour', 'button_url' => '/custom-tours', 'display_order' => 100],
            'whatsapp_cta' => ['heading' => 'Talk with a local specialist', 'subheading' => 'WhatsApp us', 'content' => 'Start a friendly conversation with our Sri Lanka travel team.', 'button_text' => 'Start a conversation', 'button_url' => $whatsappUrl, 'display_order' => 110],
        ];

        $contactSections = [
            'contact_details' => ['heading' => 'We are here to help', 'subheading' => 'Get in touch', 'content' => 'Contact our local team and start planning with confidence.', 'display_order' => 20],
            'form' => ['heading' => 'Tell us how we can help', 'subheading' => 'Send a message', 'content' => 'Whether you have a quick question or a journey in mind, we would love to hear from you.', 'display_order' => 30],
            'faqs' => ['heading' => 'Frequently asked questions', 'subheading' => 'Helpful answers', 'display_order' => 40],
        ];

        foreach (['about' => $aboutSections, 'contact' => $contactSections] as $page => $pageSections) {
            foreach ($pageSections as $sectionKey => $attributes) {
                PageSection::firstOrCreate(
                    ['page_key' => $page, 'section_key' => $sectionKey],
                    [...$attributes, 'is_active' => true],
                );
            }
        }
    }
}
