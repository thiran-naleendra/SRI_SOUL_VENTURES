<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return ['page_key' => ['required', Rule::in(['home', 'experiences', 'packages', 'destinations', 'custom_tours', 'about', 'contact'])], 'section_key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'], 'heading' => ['nullable', 'string', 'max:255'], 'subheading' => ['nullable', 'string', 'max:255'], 'content' => ['nullable', 'string'], 'button_text' => ['nullable', 'string', 'max:255'], 'button_url' => ['nullable', 'string', 'max:1000'], 'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'settings_json' => ['nullable', 'json'], 'display_order' => ['required', 'integer', 'min:0'], 'is_active' => ['required', 'boolean']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach (['heading', 'subheading', 'content', 'button_text', 'button_url', 'settings_json'] as $field) {
                if ($this->containsCode((string) $this->input($field))) {
                    $validator->errors()->add($field, 'Blade and PHP code are not allowed.');
                }
            }
        }];
    }

    private function containsCode(string $value): bool
    {
        return preg_match('/<\?(?:php|=)|\{!!|{{|@php|@endphp/i', $value) === 1;
    }
}
