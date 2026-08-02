<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;

class TestimonialController extends SimpleContentController
{
    protected string $model = Testimonial::class;

    protected string $route = 'admin.testimonials';

    protected string $title = 'Testimonial';

    protected array $fields = ['customer_name', 'country', 'testimonial', 'rating', 'trip_name', 'display_order', 'is_featured', 'is_active'];

    protected ?string $imageField = 'customer_image';

    public function store(TestimonialRequest $r): RedirectResponse
    {
        return $this->save($r);
    }

    public function update(TestimonialRequest $r, int $id): RedirectResponse
    {
        return $this->save($r, $id);
    }
}
