<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageSectionRequest;
use App\Models\PageSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    public function index(Request $r): View
    {
        Gate::authorize('viewAny', PageSection::class);
        $v = $r->validate(['search' => ['nullable', 'string', 'max:255'], 'page' => ['nullable', 'in:home,experiences,packages,destinations,custom_tours,about,contact']]);
        $items = PageSection::when($v['search'] ?? null, fn (Builder $q, $s) => $q->where(fn ($n) => $n->where('heading', 'like', "%$s%")->orWhere('section_key', 'like', "%$s%")))->when($v['page'] ?? null, fn ($q, $p) => $q->where('page_key', $p))->orderBy('page_key')->orderBy('display_order')->paginate(20)->withQueryString();

        return view('admin.page-sections.index', compact('items'));
    }

    public function create(): View
    {
        Gate::authorize('create', PageSection::class);

        return view('admin.page-sections.form');
    }

    public function store(PageSectionRequest $r): RedirectResponse
    {
        $d = $this->data($r);
        if ($r->hasFile('image')) {
            $d['image_path'] = $r->file('image')->store('page-sections', 'public');
        }PageSection::create($d);

        return to_route('admin.pages.index')->with('success', 'Page section created.');
    }

    public function edit(PageSection $page): View
    {
        Gate::authorize('update', $page);

        return view('admin.page-sections.form', ['section' => $page]);
    }

    public function update(PageSectionRequest $r, PageSection $page): RedirectResponse
    {
        Gate::authorize('update', $page);
        $d = $this->data($r);
        if ($r->hasFile('image')) {
            $d['image_path'] = $r->file('image')->store('page-sections', 'public');
            $old = $page->image_path;
        } $page->update($d);
        if (isset($old)) {
            Storage::disk('public')->delete($old);
        }

        return back()->with('success', 'Page section updated.');
    }

    public function destroy(PageSection $page): RedirectResponse
    {
        Gate::authorize('delete', $page);
        if ($page->image_path) {
            Storage::disk('public')->delete($page->image_path);
        }$page->delete();

        return to_route('admin.pages.index')->with('success', 'Page section deleted.');
    }

    private function data(PageSectionRequest $r): array
    {
        $d = $r->safe()->except(['image', 'settings_json']);
        $d['settings'] = $r->filled('settings_json') ? json_decode($r->string('settings_json'), true, 512, JSON_THROW_ON_ERROR) : null;

        return $d;
    }
}
