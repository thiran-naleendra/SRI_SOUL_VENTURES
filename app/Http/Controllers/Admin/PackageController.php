<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use App\Http\Requests\Admin\UpdatePackageSectionRequest;
use App\Models\Destination;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\TravelStyle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Package::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'], 'category' => ['nullable', 'integer', 'exists:package_categories,id'],
            'destination' => ['nullable', 'integer', 'exists:destinations,id'], 'active' => ['nullable', 'in:yes,no'],
            'popular' => ['nullable', 'in:yes,no'], 'featured' => ['nullable', 'in:yes,no'], 'status' => ['nullable', 'in:trashed'],
        ]);
        $packages = Package::query()->with('category')
            ->when($filters['search'] ?? null, fn (Builder $q, string $v) => $q->where(fn (Builder $n) => $n->where('title', 'like', "%{$v}%")->orWhere('slug', 'like', "%{$v}%")))
            ->when($filters['category'] ?? null, fn (Builder $q, $v) => $q->where('package_category_id', $v))
            ->when($filters['destination'] ?? null, fn (Builder $q, $v) => $q->whereHas('destinations', fn (Builder $d) => $d->whereKey($v)))
            ->when(isset($filters['active']), fn (Builder $q) => $q->where('is_active', $filters['active'] === 'yes'))
            ->when(isset($filters['popular']), fn (Builder $q) => $q->where('is_popular', $filters['popular'] === 'yes'))
            ->when(isset($filters['featured']), fn (Builder $q) => $q->where('is_featured', $filters['featured'] === 'yes'))
            ->when(($filters['status'] ?? null) === 'trashed', fn (Builder $q) => $q->onlyTrashed())
            ->orderBy('display_order')->orderBy('title')->paginate(15)->withQueryString();

        return view('admin.packages.index', array_merge($this->options(), compact('packages')));
    }

    public function create(): View
    {
        Gate::authorize('create', Package::class);

        return view('admin.packages.create', $this->options());
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $newFiles = [];
        try {
            DB::transaction(function () use ($request, &$newFiles): void {
                $data = $this->packageData($request->validated());
                $data['slug'] = $this->uniqueSlug($request->validated('slug') ?: $data['title']);
                $this->setPrimaryFiles($request, $data, $newFiles);
                $package = Package::create($data);
                $package->destinations()->sync($request->validated('destination_ids', []));
                $package->travelStyles()->sync($request->validated('travel_style_ids', []));
                $this->syncChildren($package, $request->validated(), $newFiles);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }

        return to_route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(int $package): View
    {
        $package = Package::with(['destinations', 'travelStyles', 'images', 'itineraries', 'highlights', 'inclusions', 'exclusions', 'faqs', 'reviews'])->findOrFail($package);
        Gate::authorize('update', $package);

        return view('admin.packages.edit', array_merge($this->options(), compact('package')));
    }

    public function show(int $package): RedirectResponse
    {
        $package = Package::findOrFail($package);
        Gate::authorize('update', $package);

        return to_route('admin.packages.edit', $package);
    }

    public function update(UpdatePackageRequest $request, int $package): RedirectResponse
    {
        $package = Package::findOrFail($package);
        $newFiles = [];
        $oldFiles = [];
        try {
            DB::transaction(function () use ($request, $package, &$newFiles, &$oldFiles): void {
                $data = $this->packageData($request->validated());
                $data['slug'] = $this->uniqueSlug($request->validated('slug') ?: $data['title'], $package->id);
                $this->setPrimaryFiles($request, $data, $newFiles, $oldFiles, $package);
                $package->update($data);
                $package->destinations()->sync($request->validated('destination_ids', []));
                $package->travelStyles()->sync($request->validated('travel_style_ids', []));
                $this->syncChildren($package, $request->validated(), $newFiles, $oldFiles);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }
        Storage::disk('public')->delete(array_values(array_unique(array_filter($oldFiles))));

        return to_route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    public function updateSection(UpdatePackageSectionRequest $request, int $package): JsonResponse
    {
        $package = Package::query()->findOrFail($package);
        Gate::authorize('update', $package);
        $validated = $request->validated();
        $section = $validated['section'];
        $newFiles = [];
        $oldFiles = [];

        try {
            DB::transaction(function () use ($request, $package, $validated, $section, &$newFiles, &$oldFiles): void {
                if ($section === 'basic') {
                    $data = $this->nullableStrings($this->packageData($validated), [
                        'badge_text', 'short_description', 'full_description', 'starting_price', 'discount_price', 'price_note',
                        'maximum_travelers', 'tour_type', 'physical_level', 'perfect_for',
                    ]);
                    $data['slug'] = $this->uniqueSlug($validated['slug'] ?: $data['title'], $package->id);
                    $package->update($data);
                } elseif ($section === 'relations') {
                    $package->destinations()->sync($validated['destination_ids'] ?? []);
                    $package->travelStyles()->sync($validated['travel_style_ids'] ?? []);
                } elseif ($section === 'images') {
                    $data = [];
                    if ($request->hasFile('cover_image')) {
                        $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'packages/covers', $newFiles);
                        $oldFiles[] = $package->cover_image;
                    } elseif ($request->boolean('remove_cover_image')) {
                        $data['cover_image'] = null;
                        $oldFiles[] = $package->cover_image;
                    }
                    if ($data !== []) {
                        $package->update($data);
                    }
                    $this->syncChildren($package, ['gallery' => $validated['gallery'] ?? []], $newFiles, $oldFiles);
                } elseif (in_array($section, ['itinerary', 'highlights', 'faqs', 'reviews'], true)) {
                    $input = $section === 'itinerary' ? 'itineraries' : $section;
                    $this->syncChildren($package, [$input => $validated[$input] ?? []], $newFiles, $oldFiles);
                } elseif ($section === 'items') {
                    $this->syncChildren($package, [
                        'inclusions' => $validated['inclusions'] ?? [],
                        'exclusions' => $validated['exclusions'] ?? [],
                    ], $newFiles, $oldFiles);
                } elseif ($section === 'policies') {
                    $data = $this->nullableStrings(collect($validated)->only([
                        'accommodation_summary', 'transportation_summary', 'cancellation_policy', 'support_text', 'terms_and_conditions',
                    ])->all(), ['accommodation_summary', 'transportation_summary', 'cancellation_policy', 'support_text', 'terms_and_conditions']);
                    if ($request->hasFile('itinerary_pdf')) {
                        $data['itinerary_pdf'] = $this->storeFile($request->file('itinerary_pdf'), 'packages/pdfs', $newFiles);
                        $oldFiles[] = $package->itinerary_pdf;
                    } elseif ($request->boolean('remove_itinerary_pdf')) {
                        $data['itinerary_pdf'] = null;
                        $oldFiles[] = $package->itinerary_pdf;
                    }
                    $package->update($data);
                } elseif ($section === 'seo') {
                    $package->update($this->nullableStrings(collect($validated)->only(['meta_title', 'meta_description'])->all(), ['meta_title', 'meta_description']));
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }

        Storage::disk('public')->delete(array_values(array_unique(array_filter($oldFiles))));

        return response()->json(['message' => 'Package section saved successfully.']);
    }

    public function destroy(int $package): RedirectResponse
    {
        $package = Package::findOrFail($package);
        Gate::authorize('delete', $package);
        $package->delete();

        return to_route('admin.packages.index')->with('success', 'Package moved to trash.');
    }

    public function restore(int $package): RedirectResponse
    {
        $package = Package::onlyTrashed()->findOrFail($package);
        Gate::authorize('restore', $package);
        $package->restore();

        return to_route('admin.packages.index', ['status' => 'trashed'])->with('success', 'Package restored successfully.');
    }

    public function toggle(int $package): RedirectResponse
    {
        $package = Package::findOrFail($package);
        Gate::authorize('update', $package);
        $package->update(['is_active' => ! $package->is_active]);

        return back()->with('success', 'Package status updated.');
    }

    private function packageData(array $v): array
    {
        return collect($v)->only(['package_category_id', 'title', 'badge_text', 'short_description', 'full_description', 'days', 'nights', 'starting_price', 'discount_price', 'currency', 'price_note', 'minimum_travelers', 'maximum_travelers', 'tour_type', 'physical_level', 'perfect_for', 'accommodation_summary', 'transportation_summary', 'cancellation_policy', 'support_text', 'terms_and_conditions', 'is_featured', 'is_popular', 'is_customizable', 'is_active', 'display_order', 'meta_title', 'meta_description'])->all();
    }

    private function nullableStrings(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function setPrimaryFiles(StorePackageRequest $request, array &$data, array &$new, array &$old = [], ?Package $package = null): void
    {
        foreach ([['cover_image', 'packages/covers'], ['itinerary_pdf', 'packages/pdfs']] as [$field, $folder]) {
            if ($request->hasFile($field)) {
                $data[$field] = $this->storeFile($request->file($field), $folder, $new);
                if ($package?->{$field}) {
                    $old[] = $package->{$field};
                }
            } elseif ($package && $request->boolean("remove_{$field}")) {
                $data[$field] = null;
                if ($package->{$field}) {
                    $old[] = $package->{$field};
                }
            }
        }
    }

    private function syncChildren(Package $package, array $v, array &$new, array &$old = []): void
    {
        $this->syncFileRows($package, 'images', $v['gallery'] ?? [], ['alt_text', 'caption'], 'image', 'image_path', 'packages/gallery', $new, $old, null);
        $itineraryIds = collect($v['itineraries'] ?? [])->pluck('id')->filter()->all();
        if ($itineraryIds) {
            $package->itineraries()->whereKey($itineraryIds)->increment('day_number', 10000);
        }
        $this->syncFileRows($package, 'itineraries', $v['itineraries'] ?? [], ['day_number', 'title', 'description', 'destination_name', 'accommodation_name', 'meals'], 'image', 'image_path', 'packages/itineraries', $new, $old, 'title');
        $this->syncFileRows($package, 'highlights', $v['highlights'] ?? [], ['title', 'alt_text'], 'image', 'image_path', 'packages/highlights', $new, $old, 'title');
        $this->syncFileRows($package, 'reviews', $v['reviews'] ?? [], ['customer_name', 'country', 'rating', 'review', 'is_approved'], 'customer_image', 'customer_image', 'packages/reviews', $new, $old, 'customer_name');
        foreach (['inclusions', 'exclusions'] as $name) {
            $this->syncRows($package, $name, $v[$name] ?? [], ['item'], 'item');
        }
        $this->syncRows($package, 'faqs', $v['faqs'] ?? [], ['question', 'answer', 'is_active'], 'question');
    }

    private function syncFileRows(Package $package, string $relation, array $rows, array $fields, string $inputFile, string $columnFile, string $folder, array &$new, array &$old, ?string $required): void
    {
        foreach ($rows as $row) {
            $relationQuery = $package->{$relation}();
            $childId = $row['id'] ?? null;
            $child = $childId ? $relationQuery->find($childId) : null;

            if ($childId && ! $child) {
                Log::warning('Skipped stale package child reference during update.', [
                    'package_id' => $package->id,
                    'relation' => $relation,
                    'child_id' => $childId,
                ]);

                continue;
            }
            if ($row['_remove'] ?? false) {
                if ($child) {
                    $old[] = $child->{$columnFile};
                    $child->delete();
                }

                continue;
            }
            if (! $child && $required && blank($row[$required] ?? null)) {
                continue;
            }
            if (! $child && ! $required && ! ($row[$inputFile] ?? null)) {
                continue;
            }
            $data = collect($row)->only($fields)->all();
            $data['display_order'] = $row['display_order'] ?? 0;
            if (($row[$inputFile] ?? null) instanceof UploadedFile) {
                $data[$columnFile] = $this->storeFile($row[$inputFile], $folder, $new);
                if ($child?->{$columnFile}) {
                    $old[] = $child->{$columnFile};
                }
            }
            $child ? $child->update($data) : $relationQuery->create($data);
        }
    }

    private function syncRows(Package $package, string $relation, array $rows, array $fields, string $required): void
    {
        foreach ($rows as $row) {
            $relationQuery = $package->{$relation}();
            $childId = $row['id'] ?? null;
            $child = $childId ? $relationQuery->find($childId) : null;

            if ($childId && ! $child) {
                Log::warning('Skipped stale package child reference during update.', [
                    'package_id' => $package->id,
                    'relation' => $relation,
                    'child_id' => $childId,
                ]);

                continue;
            }
            if ($row['_remove'] ?? false) {
                $child?->delete();

                continue;
            }
            if (! $child && blank($row[$required] ?? null)) {
                continue;
            }
            $data = collect($row)->only($fields)->all();
            $data['display_order'] = $row['display_order'] ?? 0;
            $child ? $child->update($data) : $relationQuery->create($data);
        }
    }

    private function uniqueSlug(string $value, ?int $ignore = null): string
    {
        $base = Str::slug($value) ?: 'package';
        $slug = $base;
        $suffix = 2;
        while (Package::withTrashed()->where('slug', $slug)->when($ignore, fn (Builder $q) => $q->whereKeyNot($ignore))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function storeFile(UploadedFile $file, string $folder, array &$new): string
    {
        $path = $file->store($folder, 'public');
        if (! is_string($path)) {
            throw new \RuntimeException('The file could not be stored.');
        } $new[] = $path;

        return $path;
    }

    private function options(): array
    {
        return ['categories' => PackageCategory::where('is_active', true)->orderBy('display_order')->get(), 'destinations' => Destination::where('is_active', true)->orderBy('display_order')->get(), 'travelStyles' => TravelStyle::where('is_active', true)->orderBy('display_order')->get()];
    }
}
