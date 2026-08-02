<?php

namespace Database\Seeders;

use App\Models\DestinationRegion;
use App\Models\DomainModel;
use App\Models\ExperienceCategory;
use App\Models\PackageCategory;
use App\Models\TravelStyle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $this->seed(DestinationRegion::class, [
            'South Coast', 'Hill Country', 'Cultural Triangle', 'Wildlife and Nature',
            'East Coast', 'North Sri Lanka', 'West Coast', 'Colombo and Around',
        ], 'short_description');

        $this->seed(ExperienceCategory::class, [
            'Wildlife and Nature', 'Adventure', 'Culture and Heritage', 'Food and Local Life',
            'Relax and Wellness', 'Luxury Experiences', 'Photography', 'Beach Activities',
        ]);

        $this->seed(TravelStyle::class, [
            'Adventure', 'Relax', 'Honeymoon', 'Family', 'Luxury', 'Backpacking',
            'Photography', 'Wellness', 'Culture and Heritage',
        ]);

        $this->seed(PackageCategory::class, [
            'Short Escapes', 'Sri Lanka Highlights', 'Adventure Tours', 'Complete Sri Lanka',
            'Honeymoon Packages', 'Family Packages', 'Luxury Tours',
        ]);
    }

    /** @param class-string<DomainModel> $modelClass */
    private function seed(string $modelClass, array $names, string $descriptionField = 'description'): void
    {
        foreach ($names as $index => $name) {
            $model = $modelClass::withTrashed()->firstOrNew(['slug' => Str::slug($name)]);
            $model->fill([
                'name' => $name,
                $descriptionField => null,
                'display_order' => ($index + 1) * 10,
                'is_active' => true,
            ]);
            $model->save();

            if ($model->trashed()) {
                $model->restore();
            }
        }
    }
}
