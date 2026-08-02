<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('testimonials.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured'), 'is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return ['customer_name' => ['required', 'string', 'max:255'], 'country' => ['nullable', 'string', 'max:255'], 'testimonial' => ['required', 'string'], 'rating' => ['required', 'integer', 'between:1,5'], 'customer_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'trip_name' => ['nullable', 'string', 'max:255'], 'display_order' => ['required', 'integer', 'min:0'], 'is_featured' => ['required', 'boolean'], 'is_active' => ['required', 'boolean']];
    }
}
