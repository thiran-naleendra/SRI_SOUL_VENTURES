<?php

namespace App\Http\Requests\Admin;

use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Package::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => strtoupper((string) $this->input('currency', 'USD')),
            'is_featured' => $this->boolean('is_featured'), 'is_popular' => $this->boolean('is_popular'),
            'is_customizable' => $this->boolean('is_customizable'), 'is_active' => $this->boolean('is_active'),
            'remove_cover_image' => $this->boolean('remove_cover_image'), 'remove_itinerary_pdf' => $this->boolean('remove_itinerary_pdf'),
        ]);
    }

    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        return [
            'package_category_id' => ['required', 'integer', 'exists:package_categories,id'],
            'destination_ids' => ['nullable', 'array', 'max:100'], 'destination_ids.*' => ['integer', 'distinct', 'exists:destinations,id'],
            'travel_style_ids' => ['nullable', 'array', 'max:30'], 'travel_style_ids.*' => ['integer', 'distinct', 'exists:travel_styles,id'],
            'title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'badge_text' => ['nullable', 'string', 'max:100'], 'short_description' => ['nullable', 'string', 'max:2000'], 'full_description' => ['nullable', 'string'],
            'days' => ['required', 'integer', 'min:1', 'max:65535'], 'nights' => ['required', 'integer', 'min:0', 'max:65535'],
            'starting_price' => ['nullable', 'numeric', 'min:0'], 'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:starting_price'],
            'currency' => ['required', 'string', 'size:3'], 'price_note' => ['nullable', 'string', 'max:255'],
            'minimum_travelers' => ['required', 'integer', 'min:1', 'max:65535'], 'maximum_travelers' => ['nullable', 'integer', 'gte:minimum_travelers', 'max:65535'],
            'tour_type' => ['nullable', 'string', 'max:255'], 'physical_level' => ['nullable', 'string', 'max:255'], 'perfect_for' => ['nullable', 'string'],
            'accommodation_summary' => ['nullable', 'string'], 'transportation_summary' => ['nullable', 'string'], 'cancellation_policy' => ['nullable', 'string'],
            'support_text' => ['nullable', 'string'], 'terms_and_conditions' => ['nullable', 'string'],
            'cover_image' => $image, 'remove_cover_image' => ['boolean'],
            'itinerary_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], 'remove_itinerary_pdf' => ['boolean'],
            'is_featured' => ['required', 'boolean'], 'is_popular' => ['required', 'boolean'], 'is_customizable' => ['required', 'boolean'], 'is_active' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:65535'], 'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:2000'],
            'gallery' => ['nullable', 'array', 'max:50'], 'gallery.*.id' => ['nullable', 'integer'], 'gallery.*.image' => $image,
            'gallery.*.alt_text' => ['nullable', 'string', 'max:255'], 'gallery.*.caption' => ['nullable', 'string', 'max:1000'], 'gallery.*.display_order' => ['nullable', 'integer', 'min:0'], 'gallery.*._remove' => ['nullable', 'boolean'],
            'itineraries' => ['nullable', 'array', 'max:100'], 'itineraries.*.id' => ['nullable', 'integer'], 'itineraries.*.day_number' => ['required_with:itineraries.*.title', 'nullable', 'integer', 'min:1'],
            'itineraries.*.title' => ['nullable', 'string', 'max:255'], 'itineraries.*.description' => ['nullable', 'string'], 'itineraries.*.destination_name' => ['nullable', 'string', 'max:255'],
            'itineraries.*.accommodation_name' => ['nullable', 'string', 'max:255'], 'itineraries.*.meals' => ['nullable', 'string', 'max:255'], 'itineraries.*.image' => $image,
            'itineraries.*.display_order' => ['nullable', 'integer', 'min:0'], 'itineraries.*._remove' => ['nullable', 'boolean'],
            'highlights' => ['nullable', 'array', 'max:50'], 'highlights.*.id' => ['nullable', 'integer'], 'highlights.*.title' => ['nullable', 'string', 'max:255'],
            'highlights.*.image' => $image, 'highlights.*.alt_text' => ['nullable', 'string', 'max:255'], 'highlights.*.display_order' => ['nullable', 'integer', 'min:0'], 'highlights.*._remove' => ['nullable', 'boolean'],
            'inclusions' => ['nullable', 'array', 'max:100'], 'inclusions.*.id' => ['nullable', 'integer'], 'inclusions.*.item' => ['nullable', 'string', 'max:2000'], 'inclusions.*.display_order' => ['nullable', 'integer', 'min:0'], 'inclusions.*._remove' => ['nullable', 'boolean'],
            'exclusions' => ['nullable', 'array', 'max:100'], 'exclusions.*.id' => ['nullable', 'integer'], 'exclusions.*.item' => ['nullable', 'string', 'max:2000'], 'exclusions.*.display_order' => ['nullable', 'integer', 'min:0'], 'exclusions.*._remove' => ['nullable', 'boolean'],
            'faqs' => ['nullable', 'array', 'max:100'], 'faqs.*.id' => ['nullable', 'integer'], 'faqs.*.question' => ['nullable', 'string', 'max:2000'], 'faqs.*.answer' => ['nullable', 'string'],
            'faqs.*.is_active' => ['nullable', 'boolean'], 'faqs.*.display_order' => ['nullable', 'integer', 'min:0'], 'faqs.*._remove' => ['nullable', 'boolean'],
            'reviews' => ['nullable', 'array', 'max:100'], 'reviews.*.id' => ['nullable', 'integer'], 'reviews.*.customer_name' => ['nullable', 'string', 'max:255'],
            'reviews.*.country' => ['nullable', 'string', 'max:255'], 'reviews.*.rating' => ['nullable', 'integer', 'between:1,5'], 'reviews.*.review' => ['nullable', 'string'],
            'reviews.*.customer_image' => $image, 'reviews.*.is_approved' => ['nullable', 'boolean'], 'reviews.*.display_order' => ['nullable', 'integer', 'min:0'], 'reviews.*._remove' => ['nullable', 'boolean'],
        ];
    }
}
