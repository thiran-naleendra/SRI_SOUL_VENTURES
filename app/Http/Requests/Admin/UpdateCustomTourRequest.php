<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('custom_tours.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['mark_contacted' => $this->boolean('mark_contacted'), 'mark_quotation_sent' => $this->boolean('mark_quotation_sent'), 'mark_confirmed' => $this->boolean('mark_confirmed')]);
    }

    public function rules(): array
    {
        return ['assigned_user_id' => ['nullable', 'integer', 'exists:users,id'], 'status' => ['required', Rule::in(['new', 'contacted', 'planning', 'quotation_sent', 'confirmed', 'completed', 'cancelled'])], 'admin_notes' => ['nullable', 'string'], 'mark_contacted' => ['boolean'], 'mark_quotation_sent' => ['boolean'], 'mark_confirmed' => ['boolean']];
    }
}
