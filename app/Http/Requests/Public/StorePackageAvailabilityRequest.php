<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'adults' => $this->input('adults', 1),
            'children' => $this->input('children', 0),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'preferred_start_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_end_date' => ['nullable', 'date', 'after_or_equal:preferred_start_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:100'],
            'children' => ['required', 'integer', 'min:0', 'max:100'],
            'message' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }
}
