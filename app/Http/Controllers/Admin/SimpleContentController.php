<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

abstract class SimpleContentController extends Controller
{
    protected string $model;

    protected string $route;

    protected string $title;

    protected array $fields;

    protected ?string $imageField = null;

    public function index(Request $r): View
    {
        Gate::authorize('viewAny', $this->model);
        $v = $r->validate(['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', 'in:active,inactive,trashed']]);
        $items = ($this->model)::query()->when($v['search'] ?? null, fn (Builder $q, $s) => $q->where($this->fields[0], 'like', "%$s%"))->when(($v['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))->when(($v['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))->when(($v['status'] ?? null) === 'trashed', fn ($q) => $q->onlyTrashed())->orderBy('display_order')->paginate(20)->withQueryString();

        return view('admin.simple-content.index', $this->view(compact('items')));
    }

    public function create(): View
    {
        Gate::authorize('create', $this->model);

        return view('admin.simple-content.form', $this->view());
    }

    public function edit(int $id): View
    {
        $item = ($this->model)::findOrFail($id);
        Gate::authorize('update', $item);

        return view('admin.simple-content.form', $this->view(compact('item')));
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = ($this->model)::findOrFail($id);
        Gate::authorize('delete', $item);
        $item->delete();

        return to_route($this->route.'.index')->with('success', $this->title.' moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $item = ($this->model)::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $item);
        $item->restore();

        return to_route($this->route.'.index', ['status' => 'trashed'])->with('success', $this->title.' restored.');
    }

    protected function save(FormRequest $r, ?int $id = null): RedirectResponse
    {
        $item = $id ? ($this->model)::findOrFail($id) : new $this->model;
        $data = $r->safe()->only($this->fields);
        if ($this->imageField && $r->hasFile($this->imageField)) {
            $data[$this->imageField] = $r->file($this->imageField)->store('content', 'public');
            $old = $item->{$this->imageField};
        }$item->fill($data)->save();
        if (isset($old)) {
            Storage::disk('public')->delete($old);
        }

        return to_route($this->route.'.index')->with('success', $this->title.' saved.');
    }

    private function view(array $d = []): array
    {
        return array_merge(['title' => $this->title, 'routePrefix' => $this->route, 'fields' => $this->fields, 'imageField' => $this->imageField], $d);
    }
}
