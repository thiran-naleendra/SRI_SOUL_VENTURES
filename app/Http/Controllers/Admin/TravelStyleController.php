<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TravelStyleRequest;
use App\Models\TravelStyle;
use Illuminate\Http\RedirectResponse;

class TravelStyleController extends TaxonomyController
{
    protected string $modelClass = TravelStyle::class;

    protected string $routePrefix = 'admin.travel-styles';

    protected string $title = 'Travel Styles';

    protected string $singular = 'Travel style';

    protected string $permissionPrefix = 'experiences';

    protected bool $hasIcon = true;

    public function store(TravelStyleRequest $request): RedirectResponse
    {
        return $this->storeFrom($request);
    }

    public function update(TravelStyleRequest $request, int $id): RedirectResponse
    {
        return $this->updateFrom($request, $id);
    }
}
