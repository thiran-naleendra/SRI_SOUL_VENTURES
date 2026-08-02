<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('faqs.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return ['category' => ['nullable', 'string', 'max:255'], 'question' => ['required', 'string', 'max:2000'], 'answer' => ['required', 'string'], 'display_order' => ['required', 'integer', 'min:0'], 'is_active' => ['required', 'boolean']];
    }
}
