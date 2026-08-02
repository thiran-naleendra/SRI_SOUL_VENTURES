<?php

namespace App\Http\Requests\Admin;

use App\Models\Experience;
use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Experience::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => strtoupper((string) $this->input('currency', 'USD')),
            'is_featured' => $this->boolean('is_featured'),
            'is_popular' => $this->boolean('is_popular'),
            'is_active' => $this->boolean('is_active'),
            'remove_cover_image' => $this->boolean('remove_cover_image'),
        ]);
    }

    public function rules(): array
    {
        return [
            'experience_category_id' => ['required', 'integer', 'exists:experience_categories,id'],
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'travel_style_ids' => ['nullable', 'array', 'max:30'],
            'travel_style_ids.*' => ['integer', 'distinct', 'exists:travel_styles,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'badge_text' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'full_description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'duration_value' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'duration_unit' => ['nullable', 'string', 'max:30'],
            'starting_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_cover_image' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'important_information' => ['nullable', 'string'],
            'is_featured' => ['required', 'boolean'],
            'is_popular' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*.id' => ['nullable', 'integer'],
            'gallery.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery.*.alt_text' => ['nullable', 'string', 'max:255'],
            'gallery.*.caption' => ['nullable', 'string', 'max:1000'],
            'gallery.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'gallery.*._remove' => ['nullable', 'boolean'],
            'highlights' => ['nullable', 'array', 'max:50'],
            'highlights.*.id' => ['nullable', 'integer'],
            'highlights.*.item' => ['nullable', 'string', 'max:2000'],
            'highlights.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'highlights.*._remove' => ['nullable', 'boolean'],
            'inclusions' => ['nullable', 'array', 'max:50'],
            'inclusions.*.id' => ['nullable', 'integer'],
            'inclusions.*.item' => ['nullable', 'string', 'max:2000'],
            'inclusions.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'inclusions.*._remove' => ['nullable', 'boolean'],
            'exclusions' => ['nullable', 'array', 'max:50'],
            'exclusions.*.id' => ['nullable', 'integer'],
            'exclusions.*.item' => ['nullable', 'string', 'max:2000'],
            'exclusions.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'exclusions.*._remove' => ['nullable', 'boolean'],
        ];
    }
}
