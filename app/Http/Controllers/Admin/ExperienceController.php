<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExperienceRequest;
use App\Http\Requests\Admin\UpdateExperienceRequest;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\TravelStyle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ExperienceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Experience::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:experience_categories,id'],
            'destination' => ['nullable', 'integer', 'exists:destinations,id'],
            'featured' => ['nullable', 'in:yes,no'],
            'status' => ['nullable', 'in:active,inactive,trashed'],
        ]);

        $experiences = Experience::query()->with(['category', 'destination'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $nested) => $nested->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->when($filters['category'] ?? null, fn (Builder $query, int|string $id) => $query->where('experience_category_id', $id))
            ->when($filters['destination'] ?? null, fn (Builder $query, int|string $id) => $query->where('destination_id', $id))
            ->when(($filters['featured'] ?? null) === 'yes', fn (Builder $query) => $query->where('is_featured', true))
            ->when(($filters['featured'] ?? null) === 'no', fn (Builder $query) => $query->where('is_featured', false))
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when(($filters['status'] ?? null) === 'trashed', fn (Builder $query) => $query->onlyTrashed())
            ->orderBy('display_order')->orderBy('title')->paginate(15)->withQueryString();

        return view('admin.experiences.index', array_merge($this->options(), compact('experiences')));
    }

    public function create(): View
    {
        Gate::authorize('create', Experience::class);

        return view('admin.experiences.create', $this->options());
    }

    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        $newFiles = [];
        try {
            DB::transaction(function () use ($request, &$newFiles): void {
                $data = $this->experienceData($request->validated());
                $data['slug'] = $this->uniqueSlug($request->validated('slug') ?: $data['title']);
                if ($request->hasFile('cover_image')) {
                    $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'experiences/covers', $newFiles);
                }
                $experience = Experience::create($data);
                $experience->travelStyles()->sync($request->validated('travel_style_ids', []));
                $this->syncChildren($experience, $request->validated(), $newFiles);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }

        return to_route('admin.experiences.index')->with('success', 'Experience created successfully.');
    }

    public function edit(int $experience): View
    {
        $experience = Experience::query()->with(['travelStyles', 'images', 'highlights', 'inclusions', 'exclusions'])->findOrFail($experience);
        Gate::authorize('update', $experience);

        return view('admin.experiences.edit', array_merge($this->options(), compact('experience')));
    }

    public function update(UpdateExperienceRequest $request, int $experience): RedirectResponse
    {
        $experience = Experience::query()->findOrFail($experience);
        $newFiles = [];
        $oldFiles = [];
        try {
            DB::transaction(function () use ($request, $experience, &$newFiles, &$oldFiles): void {
                $data = $this->experienceData($request->validated());
                $data['slug'] = $this->uniqueSlug($request->validated('slug') ?: $data['title'], $experience->id);
                if ($request->hasFile('cover_image')) {
                    $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'experiences/covers', $newFiles);
                    if ($experience->cover_image) {
                        $oldFiles[] = $experience->cover_image;
                    }
                } elseif ($request->boolean('remove_cover_image')) {
                    $data['cover_image'] = null;
                    if ($experience->cover_image) {
                        $oldFiles[] = $experience->cover_image;
                    }
                }
                $experience->update($data);
                $experience->travelStyles()->sync($request->validated('travel_style_ids', []));
                $this->syncChildren($experience, $request->validated(), $newFiles, $oldFiles);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }
        Storage::disk('public')->delete(array_values(array_unique(array_filter($oldFiles))));

        return to_route('admin.experiences.index')->with('success', 'Experience updated successfully.');
    }

    public function destroy(int $experience): RedirectResponse
    {
        $experience = Experience::query()->findOrFail($experience);
        Gate::authorize('delete', $experience);
        $experience->delete();

        return to_route('admin.experiences.index')->with('success', 'Experience moved to trash.');
    }

    public function restore(int $experience): RedirectResponse
    {
        $experience = Experience::onlyTrashed()->findOrFail($experience);
        Gate::authorize('restore', $experience);
        $experience->restore();

        return to_route('admin.experiences.index', ['status' => 'trashed'])->with('success', 'Experience restored successfully.');
    }

    public function toggle(int $experience): RedirectResponse
    {
        $experience = Experience::query()->findOrFail($experience);
        Gate::authorize('update', $experience);
        $experience->update(['is_active' => ! $experience->is_active]);

        return back()->with('success', 'Experience status updated.');
    }

    private function experienceData(array $validated): array
    {
        return collect($validated)->only([
            'experience_category_id', 'destination_id', 'title', 'badge_text', 'short_description',
            'full_description', 'location', 'duration_value', 'duration_unit', 'starting_price', 'currency',
            'latitude', 'longitude', 'important_information', 'is_featured', 'is_popular', 'is_active',
            'display_order', 'meta_title', 'meta_description',
        ])->all();
    }

    private function syncChildren(Experience $experience, array $validated, array &$newFiles, array &$oldFiles = []): void
    {
        foreach ($validated['gallery'] ?? [] as $row) {
            $image = isset($row['id']) ? $experience->images()->findOrFail($row['id']) : null;
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
                $data['image_path'] = $this->storeFile($row['image'], 'experiences/gallery', $newFiles);
                if ($image) {
                    $oldFiles[] = $image->image_path;
                }
            }
            $image ? $image->update($data) : $experience->images()->create($data);
        }
        foreach (['highlights', 'inclusions', 'exclusions'] as $relationName) {
            $relation = $experience->{$relationName}();
            foreach ($validated[$relationName] ?? [] as $row) {
                $child = isset($row['id']) ? $relation->findOrFail($row['id']) : null;
                if ($row['_remove'] ?? false) {
                    $child?->delete();

                    continue;
                }
                if (! $child && blank($row['item'] ?? null)) {
                    continue;
                }
                $data = ['item' => $row['item'], 'display_order' => $row['display_order'] ?? 0];
                $child ? $child->update($data) : $relation->create($data);
            }
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'experience';
        $slug = $base;
        $suffix = 2;
        while (Experience::withTrashed()->where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
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

    private function options(): array
    {
        return [
            'categories' => ExperienceCategory::query()->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(),
            'destinations' => Destination::query()->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(),
            'travelStyles' => TravelStyle::query()->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(),
        ];
    }
}
