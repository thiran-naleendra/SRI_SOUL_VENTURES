<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'designation' => ['required', 'string', 'max:255'], 'biography' => ['nullable', 'string'], 'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'], 'linkedin_url' => ['nullable', 'url', 'max:1000'], 'instagram_url' => ['nullable', 'url', 'max:1000'], 'display_order' => ['required', 'integer', 'min:0'], 'is_active' => ['required', 'boolean']];
    }
}
