<?php

namespace App\Http\Requests\Admin;

class PackageCategoryRequest extends TaxonomyRequest
{
    protected string $permission = 'packages.create';

    public function authorize(): bool
    {
        $this->permission = $this->isMethod('POST') ? 'packages.create' : 'packages.update';

        return parent::authorize();
    }
}
