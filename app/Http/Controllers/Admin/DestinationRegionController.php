<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DestinationRegionRequest;
use App\Models\DestinationRegion;
use Illuminate\Http\RedirectResponse;

class DestinationRegionController extends TaxonomyController
{
    protected string $modelClass = DestinationRegion::class;

    protected string $routePrefix = 'admin.destination-regions';

    protected string $title = 'Destination Regions';

    protected string $singular = 'Destination region';

    protected string $permissionPrefix = 'destinations';

    protected string $descriptionField = 'short_description';

    protected bool $hasIcon = true;

    public function store(DestinationRegionRequest $request): RedirectResponse
    {
        return $this->storeFrom($request);
    }

    public function update(DestinationRegionRequest $request, int $id): RedirectResponse
    {
        return $this->updateFrom($request, $id);
    }
}
