<?php

namespace App\Http\Requests\Admin;

use App\Models\Package;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends StorePackageRequest
{
    public function authorize(): bool
    {
        $package = Package::query()->find($this->route('package'));

        return $package !== null && ($this->user()?->can('update', $package) ?? false);
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $id = $this->route('package');
        foreach (['gallery' => 'package_images', 'itineraries' => 'package_itineraries', 'highlights' => 'package_highlights', 'inclusions' => 'package_inclusions', 'exclusions' => 'package_exclusions', 'faqs' => 'package_faqs', 'reviews' => 'package_reviews'] as $input => $table) {
            $rules["{$input}.*.id"] = ['nullable', 'integer', Rule::exists($table, 'id')->where(fn (Builder $query) => $query->where('package_id', $id))];
        }

        return $rules;
    }
}
