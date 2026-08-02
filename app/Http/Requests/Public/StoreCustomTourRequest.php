<?php

namespace App\Http\Requests\Public;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'currency' => strtoupper((string) $this->input('currency', 'USD')),
            'adults' => $this->input('adults', 1),
            'children' => $this->input('children', 0),
        ]);
    }

    public function rules(): array
    {
        $active = fn (Builder $query) => $query->where('is_active', true)->whereNull('deleted_at');

        return [
            'package_id' => ['nullable', 'integer', Rule::exists('packages', 'id')->where($active)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'arrival_date' => ['required', 'date', 'after_or_equal:today'],
            'departure_date' => ['nullable', 'date', 'after_or_equal:arrival_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:100'],
            'children' => ['required', 'integer', 'min:0', 'max:100'],
            'destination_ids' => ['nullable', 'array', 'max:30'],
            'destination_ids.*' => ['integer', 'distinct', Rule::exists('destinations', 'id')->where($active)],
            'travel_style_ids' => ['nullable', 'array', 'max:30'],
            'travel_style_ids.*' => ['integer', 'distinct', Rule::exists('travel_styles', 'id')->where($active)],
            'budget_min' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'budget_max' => ['nullable', 'numeric', 'gte:budget_min', 'max:9999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'accommodation_preference' => ['nullable', 'string', 'max:255'],
            'transport_preference' => ['nullable', 'string', 'max:255'],
            'special_requirements' => ['nullable', 'string', 'max:5000'],
            'message' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }
}
