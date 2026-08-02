<?php

namespace App\Http\Requests\Admin;

use App\Models\Experience;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UpdateExperienceRequest extends StoreExperienceRequest
{
    public function authorize(): bool
    {
        $experience = Experience::query()->find($this->route('experience'));

        return $experience !== null && ($this->user()?->can('update', $experience) ?? false);
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $experienceId = $this->route('experience');
        $rules['gallery.*.id'] = ['nullable', 'integer', Rule::exists('experience_images', 'id')->where(fn (Builder $query) => $query->where('experience_id', $experienceId))];
        foreach (['highlights' => 'experience_highlights', 'inclusions' => 'experience_inclusions', 'exclusions' => 'experience_exclusions'] as $input => $table) {
            $rules["{$input}.*.id"] = ['nullable', 'integer', Rule::exists($table, 'id')->where(fn (Builder $query) => $query->where('experience_id', $experienceId))];
        }

        return $rules;
    }
}
