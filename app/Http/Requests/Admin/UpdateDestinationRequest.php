<?php

namespace App\Http\Requests\Admin;

use App\Models\Destination;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UpdateDestinationRequest extends StoreDestinationRequest
{
    public function authorize(): bool
    {
        $destination = Destination::query()->find($this->route('destination'));

        return $destination !== null && ($this->user()?->can('update', $destination) ?? false);
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $destinationId = $this->route('destination');
        $rules['gallery.*.id'] = ['nullable', 'integer', Rule::exists('destination_images', 'id')->where(fn (Builder $query) => $query->where('destination_id', $destinationId))];
        $rules['attractions.*.id'] = ['nullable', 'integer', Rule::exists('destination_attractions', 'id')->where(fn (Builder $query) => $query->where('destination_id', $destinationId))];
        $rules['activities.*.id'] = ['nullable', 'integer', Rule::exists('destination_activities', 'id')->where(fn (Builder $query) => $query->where('destination_id', $destinationId))];
        $rules['travel_tips.*.id'] = ['nullable', 'integer', Rule::exists('destination_travel_tips', 'id')->where(fn (Builder $query) => $query->where('destination_id', $destinationId))];

        return $rules;
    }
}
