<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\CustomTourRequest;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Models\PackageEnquiry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $data = ['monthlyEnquiries' => collect()];
        $monthlyModels = [];

        if ($user->can('destinations.view')) {
            $data['destinationStats'] = $this->activeStats(Destination::query());
        }

        if ($user->can('experiences.view')) {
            $data['experienceStats'] = $this->activeStats(Experience::query());
            $data['popularExperiences'] = Experience::query()
                ->with(['category:id,name', 'destination:id,name'])
                ->where('is_active', true)
                ->where('is_popular', true)
                ->orderBy('display_order')
                ->limit(5)
                ->get(['id', 'experience_category_id', 'destination_id', 'title', 'slug', 'starting_price', 'currency']);
        }

        if ($user->can('packages.view')) {
            $data['packageStats'] = $this->activeStats(Package::query());
            $data['popularPackages'] = Package::query()
                ->with('category:id,name')
                ->where('is_active', true)
                ->where('is_popular', true)
                ->orderBy('display_order')
                ->limit(5)
                ->get(['id', 'package_category_id', 'title', 'slug', 'days', 'starting_price', 'currency']);
        }

        if ($user->can('enquiries.view')) {
            $data['packageEnquiryStats'] = $this->statusStats(PackageEnquiry::query(), 'new');
            $data['contactEnquiryStats'] = $this->unreadStats();
            $data['recentPackageEnquiries'] = PackageEnquiry::query()
                ->with('package:id,title')
                ->latest()
                ->limit(5)
                ->get(['id', 'package_id', 'customer_name', 'email', 'status', 'created_at']);
            $data['recentContactEnquiries'] = ContactEnquiry::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'subject', 'status', 'is_read', 'created_at']);
            $monthlyModels = [PackageEnquiry::class, ContactEnquiry::class];
        }

        if ($user->can('custom_tours.view')) {
            $data['customTourStats'] = $this->statusStats(CustomTourRequest::query(), 'new');
            $data['recentCustomTourRequests'] = CustomTourRequest::query()
                ->with(['package:id,title', 'assignedUser:id,name'])
                ->latest()
                ->limit(5)
                ->get(['id', 'package_id', 'assigned_user_id', 'customer_name', 'email', 'status', 'created_at']);
            $monthlyModels[] = CustomTourRequest::class;
        }

        if ($monthlyModels !== []) {
            $data['monthlyEnquiries'] = $this->monthlyEnquiries(array_unique($monthlyModels));
        }

        return view('admin.dashboard', $data);
    }

    private function activeStats(Builder $query): object
    {
        return $query->selectRaw('COUNT(*) AS total, COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active')->firstOrFail();
    }

    private function statusStats(Builder $query, string $status): object
    {
        return $query->selectRaw('COUNT(*) AS total, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS new_count', [$status])->firstOrFail();
    }

    private function unreadStats(): object
    {
        return ContactEnquiry::query()
            ->selectRaw('COUNT(*) AS total, COALESCE(SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END), 0) AS unread')
            ->firstOrFail();
    }

    /** @param array<class-string<Model>> $models */
    private function monthlyEnquiries(array $models): Collection
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(11);
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $totals = [];

        foreach ($models as $model) {
            $model::query()
                ->where('created_at', '>=', $start)
                ->selectRaw("{$monthExpression} AS month_key, COUNT(*) AS aggregate")
                ->groupByRaw($monthExpression)
                ->pluck('aggregate', 'month_key')
                ->each(function ($count, $month) use (&$totals): void {
                    $totals[$month] = ($totals[$month] ?? 0) + (int) $count;
                });
        }

        return collect(range(0, 11))->map(function (int $offset) use ($start, $totals): array {
            $month = $start->addMonths($offset);

            return ['key' => $month->format('Y-m'), 'label' => $month->format('M'), 'total' => $totals[$month->format('Y-m')] ?? 0];
        });
    }
}
