<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePackageEnquiryRequest;
use App\Models\Package;
use App\Models\PackageEnquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PackageEnquiryController extends Controller
{
    use ExportsCsv;

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.enquiries.packages.index', ['enquiries' => $this->query($filters)->latest()->paginate(20)->withQueryString(), 'packages' => Package::orderBy('title')->get(), 'statuses' => $this->statuses()]);
    }

    public function show(PackageEnquiry $packageEnquiry): View
    {
        return view('admin.enquiries.packages.show', ['enquiry' => $packageEnquiry->load('package'), 'statuses' => $this->statuses()]);
    }

    public function update(UpdatePackageEnquiryRequest $request, PackageEnquiry $packageEnquiry): RedirectResponse
    {
        $data = $request->safe()->only(['status', 'admin_notes']);
        if ($request->boolean('mark_contacted')) {
            $data['contacted_at'] = now();
        }
        $packageEnquiry->update($data);

        return back()->with('success', 'Package enquiry updated.');
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->query($this->filters($request))->latest()->get()->map(fn (PackageEnquiry $e) => [$e->id, $e->package->title, $e->customer_name, $e->email, $e->phone, $e->whatsapp_number, $e->country, $e->preferred_start_date?->toDateString(), $e->preferred_end_date?->toDateString(), $e->adults, $e->children, $e->status, $e->message, $e->admin_notes, $e->contacted_at?->toDateTimeString(), $e->created_at->toDateTimeString()]);

        return $this->csv('package-enquiries-'.now()->format('Y-m-d').'.csv', ['ID', 'Package', 'Customer', 'Email', 'Phone', 'WhatsApp', 'Country', 'Start Date', 'End Date', 'Adults', 'Children', 'Status', 'Message', 'Admin Notes', 'Contacted At', 'Created At'], $rows);
    }

    private function filters(Request $request): array
    {
        return $request->validate(['search' => ['nullable', 'string', 'max:255'], 'package' => ['nullable', 'integer', 'exists:packages,id'], 'status' => ['nullable', 'in:'.implode(',', $this->statuses())]]);
    }

    private function query(array $filters): Builder
    {
        return PackageEnquiry::query()->with('package')
            ->when($filters['search'] ?? null, fn (Builder $q, string $v) => $q->where(fn (Builder $n) => $n->where('customer_name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")->orWhere('phone', 'like', "%{$v}%")->orWhereHas('package', fn (Builder $p) => $p->where('title', 'like', "%{$v}%"))))
            ->when($filters['package'] ?? null, fn (Builder $q, $v) => $q->where('package_id', $v))->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v));
    }

    private function statuses(): array
    {
        return ['new', 'contacted', 'quotation_sent', 'confirmed', 'completed', 'cancelled'];
    }
}
