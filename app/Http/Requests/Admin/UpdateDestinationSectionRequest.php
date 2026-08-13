<?php

namespace App\Http\Requests\Admin;

use App\Models\Destination;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDestinationSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $destination = Destination::query()->find($this->route('destination'));

        return $destination !== null && ($this->user()?->can('update', $destination) ?? false);
    }

    public function rules(): array
    {
        return match ($this->input('section')) {
            'basic' => [
                'section' => ['required', 'in:basic'],
                'destination_region_id' => ['required', 'integer', 'exists:destination_regions,id'],
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
                'short_description' => ['nullable', 'string', 'max:2000'],
                'full_description' => ['nullable', 'string'],
            ],
            'cover' => [
                'section' => ['required', 'in:cover'],
                'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'remove_cover_image' => ['nullable', 'boolean'],
            ],
            'gallery' => $this->childRules('gallery', true),
            'attractions' => $this->childRules('attractions', true),
            'activities' => $this->childRules('activities'),
            'travel_tips' => $this->childRules('travel_tips'),
            'map' => [
                'section' => ['required', 'in:map'],
                'best_time_to_visit' => ['nullable', 'string', 'max:255'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ],
            'seo' => [
                'section' => ['required', 'in:seo'],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:2000'],
            ],
            'publishing' => [
                'section' => ['required', 'in:publishing'],
                'display_order' => ['required', 'integer', 'min:0', 'max:65535'],
                'is_featured' => ['required', 'boolean'],
                'is_active' => ['required', 'boolean'],
            ],
            default => ['section' => ['required', 'in:basic,cover,gallery,attractions,activities,travel_tips,map,seo,publishing']],
        };
    }

    private function childRules(string $section, bool $hasImage = false): array
    {
        $rules = [
            'section' => ['required', "in:{$section}"],
            $section => ['nullable', 'array', 'max:50'],
            "{$section}.*.id" => ['nullable', 'integer'],
            "{$section}.*.title" => ['nullable', 'string', 'max:255'],
            "{$section}.*.description" => ['nullable', 'string'],
            "{$section}.*.display_order" => ['nullable', 'integer', 'min:0', 'max:65535'],
            "{$section}.*._remove" => ['nullable', 'boolean'],
        ];

        if ($section === 'gallery') {
            unset($rules["{$section}.*.title"], $rules["{$section}.*.description"]);
            $rules["{$section}.*.alt_text"] = ['nullable', 'string', 'max:255'];
            $rules["{$section}.*.caption"] = ['nullable', 'string', 'max:1000'];
        }

        if ($section === 'activities') {
            $rules["{$section}.*.icon"] = ['nullable', 'string', 'max:255'];
        }

        if ($hasImage) {
            $rules["{$section}.*.image"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        }

        return $rules;
    }
}
