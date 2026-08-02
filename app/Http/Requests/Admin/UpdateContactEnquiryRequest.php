<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('enquiries.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_read' => $this->boolean('is_read')]);
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['new', 'contacted', 'resolved', 'spam'])], 'admin_notes' => ['nullable', 'string'], 'is_read' => ['required', 'boolean']];
    }
}
