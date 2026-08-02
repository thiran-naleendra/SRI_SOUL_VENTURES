<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WebsiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'];

        return ['website_name' => ['required', 'string', 'max:255'], 'logo' => $image, 'footer_logo' => $image, 'favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,svg', 'max:2048'], 'primary_phone' => ['nullable', 'string', 'max:50'], 'secondary_phone' => ['nullable', 'string', 'max:50'], 'primary_email' => ['nullable', 'email', 'max:255'], 'secondary_email' => ['nullable', 'email', 'max:255'], 'whatsapp_number' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string'], 'google_maps_embed_url' => ['nullable', 'url', 'max:2000'], 'business_hours' => ['nullable', 'string', 'max:255'], 'footer_description' => ['nullable', 'string'], 'copyright_text' => ['nullable', 'string', 'max:255'], 'facebook_url' => ['nullable', 'url', 'max:1000'], 'instagram_url' => ['nullable', 'url', 'max:1000'], 'youtube_url' => ['nullable', 'url', 'max:1000'], 'linkedin_url' => ['nullable', 'url', 'max:1000'], 'default_meta_title' => ['nullable', 'string', 'max:255'], 'default_meta_description' => ['nullable', 'string', 'max:2000']];
    }
}
