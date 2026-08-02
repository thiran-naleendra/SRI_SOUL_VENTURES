<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;

class FaqController extends SimpleContentController
{
    protected string $model = Faq::class;

    protected string $route = 'admin.faqs';

    protected string $title = 'FAQ';

    protected array $fields = ['question', 'category', 'answer', 'display_order', 'is_active'];

    public function store(FaqRequest $r): RedirectResponse
    {
        return $this->save($r);
    }

    public function update(FaqRequest $r, int $id): RedirectResponse
    {
        return $this->save($r, $id);
    }
}
