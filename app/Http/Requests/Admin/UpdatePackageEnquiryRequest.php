<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('enquiries.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['mark_contacted' => $this->boolean('mark_contacted')]);
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['new', 'contacted', 'quotation_sent', 'confirmed', 'completed', 'cancelled'])], 'admin_notes' => ['nullable', 'string'], 'mark_contacted' => ['boolean']];
    }
}
