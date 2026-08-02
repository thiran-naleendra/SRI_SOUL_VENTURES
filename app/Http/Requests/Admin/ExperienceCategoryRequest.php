<?php

namespace App\Http\Requests\Admin;

class ExperienceCategoryRequest extends TaxonomyRequest
{
    protected string $permission = 'experiences.create';

    public function authorize(): bool
    {
        $this->permission = $this->isMethod('POST') ? 'experiences.create' : 'experiences.update';

        return parent::authorize();
    }
}
