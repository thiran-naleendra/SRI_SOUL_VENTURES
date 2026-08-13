<?php

namespace App\Http\Requests\Admin;

use App\Models\Destination;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->filled('editing_destination_id')) {
            $destination = Destination::query()->find($this->integer('editing_destination_id'));

            return $destination !== null && ($this->user()?->can('update', $destination) ?? false);
        }

        return $this->user()?->can('create', Destination::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('_destination_payload')) {
            $payload = json_decode((string) $this->input('_destination_payload'), true);

            if (is_array($payload)) {
                unset($payload['_token'], $payload['editing_destination_id'], $payload['_destination_payload']);
                // The compact JSON payload is decoded after Laravel's global
                // ConvertEmptyStringsToNull middleware has already executed.
                // Normalize it here so nullable database fields never receive
                // an invalid empty string.
                $this->merge($this->emptyStringsToNull($payload));
            }
        }

        $this->merge([
            '_tip_only' => $this->boolean('_tip_only'),
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'remove_cover_image' => $this->boolean('remove_cover_image'),
        ]);
    }

    private function emptyStringsToNull(array $values): array
    {
        return array_map(function (mixed $value): mixed {
            if (is_array($value)) {
                return $this->emptyStringsToNull($value);
            }

            return is_string($value) && trim($value) === '' ? null : $value;
        }, $values);
    }

    public function rules(): array
    {
        if ($this->boolean('_tip_only')) {
            $destinationId = $this->integer('editing_destination_id');

            return [
                '_tip_only' => ['required', 'accepted'],
                'editing_destination_id' => ['required', 'integer', 'exists:destinations,id'],
                'tip_id' => ['nullable', 'integer', Rule::exists('destination_travel_tips', 'id')->where(fn(Builder $query) => $query->where('destination_id', $destinationId))],
                'tip_title' => ['required', 'string', 'max:255'],
                'tip_description' => ['nullable', 'string'],
                'tip_display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ];
        }

        $rules = [
            '_destination_payload' => ['nullable', 'string'],
            'editing_destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'destination_region_id' => ['required', 'integer', 'exists:destination_regions,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'full_description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_cover_image' => ['boolean'],
            'best_time_to_visit' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_featured' => ['required', 'boolean'],
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
            'attractions' => ['nullable', 'array', 'max:30'],
            'attractions.*.id' => ['nullable', 'integer'],
            'attractions.*.title' => ['required_with:attractions.*.description,attractions.*.image', 'nullable', 'string', 'max:255'],
            'attractions.*.description' => ['nullable', 'string'],
            'attractions.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'attractions.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'attractions.*._remove' => ['nullable', 'boolean'],
            'activities' => ['nullable', 'array', 'max:50'],
            'activities.*.id' => ['nullable', 'integer'],
            'activities.*.title' => ['required_with:activities.*.description,activities.*.icon', 'nullable', 'string', 'max:255'],
            'activities.*.description' => ['nullable', 'string'],
            'activities.*.icon' => ['nullable', 'string', 'max:255'],
            'activities.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'activities.*._remove' => ['nullable', 'boolean'],
            'travel_tips' => ['nullable', 'array', 'max:50'],
            'travel_tips.*.id' => ['nullable', 'integer'],
            'travel_tips.*.title' => ['required_with:travel_tips.*.description', 'nullable', 'string', 'max:255'],
            'travel_tips.*.description' => ['nullable', 'string'],
            'travel_tips.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'travel_tips.*._remove' => ['nullable', 'boolean'],
        ];

        if ($destinationId = $this->integer('editing_destination_id')) {
            $rules['gallery.*.id'] = ['nullable', 'integer', Rule::exists('destination_images', 'id')->where(fn(Builder $query) => $query->where('destination_id', $destinationId))];
            $rules['attractions.*.id'] = ['nullable', 'integer', Rule::exists('destination_attractions', 'id')->where(fn(Builder $query) => $query->where('destination_id', $destinationId))];
            $rules['activities.*.id'] = ['nullable', 'integer', Rule::exists('destination_activities', 'id')->where(fn(Builder $query) => $query->where('destination_id', $destinationId))];
            $rules['travel_tips.*.id'] = ['nullable', 'integer', Rule::exists('destination_travel_tips', 'id')->where(fn(Builder $query) => $query->where('destination_id', $destinationId))];
        }

        return $rules;
    }
}
