<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PackageCategoryRequest;
use App\Models\PackageCategory;
use Illuminate\Http\RedirectResponse;

class PackageCategoryController extends TaxonomyController
{
    protected string $modelClass = PackageCategory::class;

    protected string $routePrefix = 'admin.package-categories';

    protected string $title = 'Package Categories';

    protected string $singular = 'Package category';

    protected string $permissionPrefix = 'packages';

    public function store(PackageCategoryRequest $request): RedirectResponse
    {
        return $this->storeFrom($request);
    }

    public function update(PackageCategoryRequest $request, int $id): RedirectResponse
    {
        return $this->updateFrom($request, $id);
    }
}
