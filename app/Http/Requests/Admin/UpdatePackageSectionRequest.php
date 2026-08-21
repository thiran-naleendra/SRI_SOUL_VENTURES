<?php

namespace App\Http\Requests\Admin;

class UpdatePackageSectionRequest extends UpdatePackageRequest
{
    public function rules(): array
    {
        $section = (string) $this->input('section');
        $fields = match ($section) {
            'basic' => ['package_category_id', 'title', 'slug', 'badge_text', 'short_description', 'full_description', 'days', 'nights', 'starting_price', 'discount_price', 'currency', 'price_note', 'minimum_travelers', 'maximum_travelers', 'tour_type', 'physical_level', 'perfect_for', 'is_featured', 'is_popular', 'is_customizable', 'is_active', 'display_order'],
            'relations' => ['destination_ids', 'travel_style_ids'],
            'images' => ['cover_image', 'remove_cover_image', 'gallery'],
            'itinerary' => ['itineraries'],
            'highlights' => ['highlights'],
            'items' => ['inclusions', 'exclusions'],
            'policies' => ['accommodation_summary', 'transportation_summary', 'cancellation_policy', 'support_text', 'terms_and_conditions', 'itinerary_pdf', 'remove_itinerary_pdf'],
            'faqs' => ['faqs'],
            'reviews' => ['reviews'],
            'seo' => ['meta_title', 'meta_description'],
            default => [],
        };

        $rules = collect(parent::rules())
            ->filter(fn (array $rule, string $key): bool => collect($fields)->contains(
                fn (string $field): bool => $key === $field || str_starts_with($key, $field.'.')
            ))
            ->all();
        $rules['section'] = ['required', 'in:basic,relations,images,itinerary,highlights,items,policies,faqs,reviews,seo'];

        return $rules;
    }
}
