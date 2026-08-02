<?php

namespace App\Http\Requests\Admin;

class DestinationRegionRequest extends TaxonomyRequest
{
    protected string $permission = 'destinations.create';

    public function authorize(): bool
    {
        $this->permission = $this->isMethod('POST') ? 'destinations.create' : 'destinations.update';

        return parent::authorize();
    }
}
