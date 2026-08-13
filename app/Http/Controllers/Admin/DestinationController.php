<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDestinationRequest;
use App\Http\Requests\Admin\UpdateDestinationRequest;
use App\Http\Requests\Admin\UpdateDestinationSectionRequest;
use App\Models\Destination;
use App\Models\DestinationRegion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Destination::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'integer', 'exists:destination_regions,id'],
            'featured' => ['nullable', 'in:yes,no'],
            'status' => ['nullable', 'in:active,inactive,trashed'],
        ]);

        $destinations = Destination::query()
            ->with('region')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->when($filters['region'] ?? null, fn (Builder $query, int|string $region) => $query->where('destination_region_id', $region))
            ->when(($filters['featured'] ?? null) === 'yes', fn (Builder $query) => $query->where('is_featured', true))
            ->when(($filters['featured'] ?? null) === 'no', fn (Builder $query) => $query->where('is_featured', false))
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when(($filters['status'] ?? null) === 'trashed', fn (Builder $query) => $query->onlyTrashed())
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.destinations.index', [
            'destinations' => $destinations,
            'regions' => DestinationRegion::query()->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Destination::class);

        return view('admin.destinations.create', ['regions' => $this->regions()]);
    }

    public function store(StoreDestinationRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->boolean('_tip_only')) {
            $destination = Destination::query()->findOrFail($request->integer('editing_destination_id'));
            Gate::authorize('update', $destination);
            $tip = $request->validated('tip_id')
                ? $destination->travelTips()->findOrFail($request->integer('tip_id'))
                : $destination->travelTips()->make();

            $tip->fill([
                'title' => $request->validated('tip_title'),
                'description' => $request->validated('tip_description'),
                'display_order' => $request->validated('tip_display_order') ?? 0,
            ]);
            $tip->save();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Travel tip saved successfully.', 'tip_id' => $tip->id]);
            }

            return back()->with('success', 'Travel tip saved successfully.');
        }

        if ($request->validated('editing_destination_id')) {
            $destination = Destination::query()->findOrFail($request->integer('editing_destination_id'));

            return $this->updateExisting($request, $destination);
        }

        $newFiles = [];

        try {
            DB::transaction(function () use ($request, &$newFiles): void {
                $data = $this->destinationData($request->validated());
                $data['slug'] = $this->uniqueSlug($request->validated('slug') ?: $data['name']);

                if ($request->hasFile('cover_image')) {
                    $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'destinations/covers', $newFiles);
                }

                $destination = Destination::create($data);
                $this->syncChildren($destination, $request->validated(), $newFiles);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }

        return to_route('admin.destinations.index')->with('success', 'Destination created successfully.');
    }

    public function edit(int $destination): View
    {
        $destination = Destination::query()->with(['images', 'attractions', 'activities', 'travelTips'])->findOrFail($destination);
        Gate::authorize('update', $destination);

        return view('admin.destinations.edit', ['destination' => $destination, 'regions' => $this->regions()]);
    }

    public function update(UpdateDestinationRequest $request, int $destination): RedirectResponse
    {
        $destination = Destination::query()->findOrFail($destination);

        return $this->updateExisting($request, $destination);
    }

    public function updateSection(UpdateDestinationSectionRequest $request, int $destination): JsonResponse
    {
        $destination = Destination::query()->findOrFail($destination);
        Gate::authorize('update', $destination);
        $validated = $request->validated();
        $section = $validated['section'];
        $newFiles = [];
        $oldFiles = [];

        try {
            DB::transaction(function () use ($request, $destination, $validated, $section, &$newFiles, &$oldFiles): void {
                $this->normalizeLegacyCoordinates($destination);

                if ($section === 'basic') {
                    $data = $this->nullableStrings(collect($validated)->only([
                        'destination_region_id', 'name', 'short_description', 'full_description',
                    ])->all(), ['short_description', 'full_description']);
                    $data['slug'] = $this->uniqueSlug($validated['slug'] ?: $data['name'], $destination->id);
                    $destination->update($data);
                } elseif ($section === 'cover') {
                    $data = [];
                    if ($request->hasFile('cover_image')) {
                        $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'destinations/covers', $newFiles);
                        if ($destination->cover_image) {
                            $oldFiles[] = $destination->cover_image;
                        }
                    } elseif ($request->boolean('remove_cover_image')) {
                        $data['cover_image'] = null;
                        if ($destination->cover_image) {
                            $oldFiles[] = $destination->cover_image;
                        }
                    }
                    if ($data !== []) {
                        $destination->update($data);
                    }
                } elseif (in_array($section, ['gallery', 'attractions', 'activities', 'travel_tips'], true)) {
                    $this->syncChildren($destination, [$section => $validated[$section] ?? []], $newFiles, $oldFiles);
                } elseif ($section === 'map') {
                    $destination->update($this->nullableStrings(collect($validated)->only([
                        'best_time_to_visit', 'latitude', 'longitude',
                    ])->all(), ['best_time_to_visit', 'latitude', 'longitude']));
                } elseif ($section === 'seo') {
                    $destination->update($this->nullableStrings(collect($validated)->only([
                        'meta_title', 'meta_description',
                    ])->all(), ['meta_title', 'meta_description']));
                } elseif ($section === 'publishing') {
                    $destination->update(collect($validated)->only([
                        'display_order', 'is_featured', 'is_active',
                    ])->all());
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }

        Storage::disk('public')->delete(array_values(array_unique(array_filter($oldFiles))));

        return response()->json(['message' => 'Section saved successfully.']);
    }

    private function updateExisting(StoreDestinationRequest $request, Destination $destination): RedirectResponse
    {
        Gate::authorize('update', $destination);
        $newFiles = [];
        $oldFiles = [];

        try {
            DB::transaction(function () use ($request, $destination, &$newFiles, &$oldFiles): void {
                $this->normalizeLegacyCoordinates($destination);

                $data = $this->destinationData($request->validated());
                $data['slug'] = $this->uniqueSlug($request->validated('slug') ?: $data['name'], $destination->id);

                if ($request->hasFile('cover_image')) {
                    $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'destinations/covers', $newFiles);
                    if ($destination->cover_image) {
                        $oldFiles[] = $destination->cover_image;
                    }
                } elseif ($request->boolean('remove_cover_image')) {
                    $data['cover_image'] = null;
                    if ($destination->cover_image) {
                        $oldFiles[] = $destination->cover_image;
                    }
                }

                $destination->update($data);
                $this->syncChildren($destination, $request->validated(), $newFiles, $oldFiles);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }

        Storage::disk('public')->delete(array_values(array_unique(array_filter($oldFiles))));

        return to_route('admin.destinations.edit', $destination)->with('success', 'Destination updated successfully.');
    }

    /**
     * Older imports stored empty strings in nullable decimal columns. Laravel's
     * decimal cast cannot compare those values while saving, so repair the row
     * before Eloquent performs dirty-attribute detection.
     */
    private function normalizeLegacyCoordinates(Destination $destination): void
    {
        $attributes = $destination->getAttributes();
        $repairs = [];

        foreach (['latitude', 'longitude'] as $attribute) {
            if (($attributes[$attribute] ?? null) === '') {
                $attributes[$attribute] = null;
                $repairs[$attribute] = null;
            }
        }

        if ($repairs === []) {
            return;
        }

        DB::table($destination->getTable())
            ->where($destination->getKeyName(), $destination->getKey())
            ->update($repairs);

        $destination->setRawAttributes($attributes, true);
    }

    public function destroy(int $destination): RedirectResponse
    {
        $destination = Destination::query()->findOrFail($destination);
        Gate::authorize('delete', $destination);
        $destination->delete();

        return to_route('admin.destinations.index')->with('success', 'Destination moved to trash.');
    }

    public function restore(int $destination): RedirectResponse
    {
        $destination = Destination::onlyTrashed()->findOrFail($destination);
        Gate::authorize('restore', $destination);
        $destination->restore();

        return to_route('admin.destinations.index', ['status' => 'trashed'])->with('success', 'Destination restored successfully.');
    }

    public function toggle(int $destination): RedirectResponse
    {
        $destination = Destination::query()->findOrFail($destination);
        Gate::authorize('update', $destination);
        $destination->update(['is_active' => ! $destination->is_active]);

        return back()->with('success', 'Destination status updated.');
    }

    private function destinationData(array $validated): array
    {
        $data = collect($validated)->only([
            'destination_region_id',
            'name',
            'short_description',
            'full_description',
            'best_time_to_visit',
            'latitude',
            'longitude',
            'is_featured',
            'is_active',
            'display_order',
            'meta_title',
            'meta_description',
        ])->all();

        foreach ([
            'short_description',
            'full_description',
            'best_time_to_visit',
            'latitude',
            'longitude',
            'meta_title',
            'meta_description',
        ] as $nullableField) {
            if (array_key_exists($nullableField, $data)
                && is_string($data[$nullableField])
                && trim($data[$nullableField]) === '') {
                $data[$nullableField] = null;
            }
        }

        return $data;
    }

    private function nullableStrings(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field]) && trim($data[$field]) === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function syncChildren(Destination $destination, array $validated, array &$newFiles, array &$oldFiles = []): void
    {
        foreach ($validated['gallery'] ?? [] as $row) {
            $image = isset($row['id']) ? $destination->images()->findOrFail($row['id']) : null;
            if ($row['_remove'] ?? false) {
                if ($image) {
                    $oldFiles[] = $image->image_path;
                    $image->delete();
                }

                continue;
            }
            if (! $image && ! ($row['image'] ?? null)) {
                continue;
            }
            $data = ['alt_text' => $row['alt_text'] ?? null, 'caption' => $row['caption'] ?? null, 'display_order' => $row['display_order'] ?? 0];
            if (($row['image'] ?? null) instanceof UploadedFile) {
                $data['image_path'] = $this->storeFile($row['image'], 'destinations/gallery', $newFiles);
                if ($image) {
                    $oldFiles[] = $image->image_path;
                }
            }
            $image ? $image->update($data) : $destination->images()->create($data);
        }

        foreach ($validated['attractions'] ?? [] as $row) {
            $attraction = isset($row['id']) ? $destination->attractions()->findOrFail($row['id']) : null;
            if ($row['_remove'] ?? false) {
                if ($attraction) {
                    $oldFiles[] = $attraction->image_path;
                    $attraction->delete();
                }

                continue;
            }
            if (! $attraction && blank($row['title'] ?? null)) {
                continue;
            }
            $data = ['title' => $row['title'], 'description' => $row['description'] ?? null, 'display_order' => $row['display_order'] ?? 0];
            if (($row['image'] ?? null) instanceof UploadedFile) {
                $data['image_path'] = $this->storeFile($row['image'], 'destinations/attractions', $newFiles);
                if ($attraction?->image_path) {
                    $oldFiles[] = $attraction->image_path;
                }
            }
            $attraction ? $attraction->update($data) : $destination->attractions()->create($data);
        }

        $this->syncTextChildren($destination, 'activities', $validated['activities'] ?? [], ['title', 'description', 'icon']);
        $this->syncTextChildren($destination, 'travelTips', $validated['travel_tips'] ?? [], ['title', 'description']);
    }

    private function syncTextChildren(Destination $destination, string $relationName, array $rows, array $fields): void
    {
        $relation = $destination->{$relationName}();
        foreach ($rows as $row) {
            $child = isset($row['id']) ? $relation->findOrFail($row['id']) : null;
            if ($row['_remove'] ?? false) {
                $child?->delete();

                continue;
            }
            if (! $child && blank($row['title'] ?? null)) {
                continue;
            }
            $data = collect($row)->only($fields)->all();
            $data['display_order'] = $row['display_order'] ?? 0;
            $child ? $child->update($data) : $relation->create($data);
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'destination';
        $slug = $base;
        $suffix = 2;
        while (Destination::withTrashed()->where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function storeFile(UploadedFile $file, string $directory, array &$newFiles): string
    {
        $path = $file->store($directory, 'public');
        if (! is_string($path)) {
            throw new \RuntimeException('The image could not be stored.');
        }
        $newFiles[] = $path;

        return $path;
    }

    private function regions()
    {
        return DestinationRegion::query()->where('is_active', true)->orderBy('display_order')->orderBy('name')->get();
    }
}
