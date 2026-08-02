<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ExperienceCategoryRequest;
use App\Models\ExperienceCategory;
use Illuminate\Http\RedirectResponse;

class ExperienceCategoryController extends TaxonomyController
{
    protected string $modelClass = ExperienceCategory::class;

    protected string $routePrefix = 'admin.experience-categories';

    protected string $title = 'Experience Categories';

    protected string $singular = 'Experience category';

    protected string $permissionPrefix = 'experiences';

    protected bool $hasIcon = true;

    public function store(ExperienceCategoryRequest $request): RedirectResponse
    {
        return $this->storeFrom($request);
    }

    public function update(ExperienceCategoryRequest $request, int $id): RedirectResponse
    {
        return $this->updateFrom($request, $id);
    }
}
