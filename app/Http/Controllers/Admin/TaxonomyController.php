<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaxonomyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class TaxonomyController extends Controller
{
    protected string $modelClass;

    protected string $routePrefix;

    protected string $title;

    protected string $singular;

    protected string $permissionPrefix;

    protected string $descriptionField = 'description';

    protected bool $hasIcon = false;

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,trashed'],
        ]);

        $items = $this->query()
            ->when($validated['search'] ?? null, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when(($validated['status'] ?? null) === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when(($validated['status'] ?? null) === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when(($validated['status'] ?? null) === 'trashed', fn (Builder $query) => $query->onlyTrashed())
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.taxonomies.index', $this->viewData(compact('items')));
    }

    public function create(): View
    {
        return view('admin.taxonomies.create', $this->viewData());
    }

    public function edit(int $id): View
    {
        $item = $this->query()->findOrFail($id);

        return view('admin.taxonomies.edit', $this->viewData(compact('item')));
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->query()->findOrFail($id)->delete();

        return to_route("{$this->routePrefix}.index")->with('success', "{$this->singular} moved to trash.");
    }

    public function restore(int $id): RedirectResponse
    {
        $this->query()->onlyTrashed()->findOrFail($id)->restore();

        return to_route("{$this->routePrefix}.index", ['status' => 'trashed'])->with('success', "{$this->singular} restored successfully.");
    }

    public function toggle(int $id): RedirectResponse
    {
        $item = $this->query()->findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);

        return back()->with('success', "{$this->singular} status updated.");
    }

    protected function storeFrom(TaxonomyRequest $request): RedirectResponse
    {
        $data = $this->data($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $this->query()->create($data);

        return to_route("{$this->routePrefix}.index")->with('success', "{$this->singular} created successfully.");
    }

    protected function updateFrom(TaxonomyRequest $request, int $id): RedirectResponse
    {
        $item = $this->query()->findOrFail($id);
        $data = $this->data($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $item->getKey());
        $item->update($data);

        return to_route("{$this->routePrefix}.index")->with('success', "{$this->singular} updated successfully.");
    }

    protected function data(TaxonomyRequest $request): array
    {
        $validated = $request->validated();
        $data = [
            'name' => $validated['name'],
            $this->descriptionField => $validated[$this->descriptionField] ?? null,
            'display_order' => $validated['display_order'],
            'is_active' => $validated['is_active'],
        ];

        if ($this->hasIcon) {
            $data['icon'] = $validated['icon'] ?? null;
        }

        return $data;
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while ($this->query()->withTrashed()->where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected function query(): Builder
    {
        return ($this->modelClass)::query();
    }

    protected function viewData(array $data = []): array
    {
        return array_merge([
            'title' => $this->title,
            'singular' => $this->singular,
            'routePrefix' => $this->routePrefix,
            'descriptionField' => $this->descriptionField,
            'hasIcon' => $this->hasIcon,
            'permissionPrefix' => $this->permissionPrefix,
        ], $data);
    }
}
