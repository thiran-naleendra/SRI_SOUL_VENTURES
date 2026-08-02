<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomTourRequest as UpdateRequest;
use App\Models\CustomTourRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomTourRequestController extends Controller
{
    use ExportsCsv;

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.enquiries.custom-tours.index', ['requests' => $this->query($filters)->latest()->paginate(20)->withQueryString(), 'consultants' => $this->consultants(), 'statuses' => $this->statuses()]);
    }

    public function show(CustomTourRequest $customTourRequest): View
    {
        return view('admin.enquiries.custom-tours.show', ['tourRequest' => $customTourRequest->load(['package', 'assignedUser', 'destinations', 'travelStyles']), 'consultants' => $this->consultants(), 'statuses' => $this->statuses()]);
    }

    public function update(UpdateRequest $request, CustomTourRequest $customTourRequest): RedirectResponse
    {
        $data = $request->safe()->only(['assigned_user_id', 'status', 'admin_notes']);
        if ($request->boolean('mark_contacted')) {
            $data['contacted_at'] = now();
        }
        if ($request->boolean('mark_quotation_sent')) {
            $data['quotation_sent_at'] = now();
        }
        if ($request->boolean('mark_confirmed')) {
            $data['confirmed_at'] = now();
        }
        $customTourRequest->update($data);

        return back()->with('success', 'Custom tour request updated.');
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->query($this->filters($request))->latest()->get()->map(fn (CustomTourRequest $r) => [$r->id, $r->customer_name, $r->email, $r->phone, $r->whatsapp_number, $r->country, $r->arrival_date?->toDateString(), $r->departure_date?->toDateString(), $r->adults, $r->children, $r->budget_min, $r->budget_max, $r->currency, $r->status, $r->assignedUser?->name, $r->destinations->pluck('name')->join('; '), $r->travelStyles->pluck('name')->join('; '), $r->message, $r->admin_notes, $r->created_at->toDateTimeString()]);

        return $this->csv('custom-tour-requests-'.now()->format('Y-m-d').'.csv', ['ID', 'Customer', 'Email', 'Phone', 'WhatsApp', 'Country', 'Arrival', 'Departure', 'Adults', 'Children', 'Budget Min', 'Budget Max', 'Currency', 'Status', 'Consultant', 'Destinations', 'Travel Styles', 'Message', 'Admin Notes', 'Created At'], $rows);
    }

    private function filters(Request $request): array
    {
        return $request->validate(['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', 'in:'.implode(',', $this->statuses())], 'consultant' => ['nullable', 'integer', 'exists:users,id']]);
    }

    private function query(array $filters): Builder
    {
        return CustomTourRequest::query()->with(['assignedUser', 'destinations', 'travelStyles'])
            ->when($filters['search'] ?? null, fn (Builder $q, string $v) => $q->where(fn (Builder $n) => $n->where('customer_name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")->orWhere('phone', 'like', "%{$v}%")->orWhere('country', 'like', "%{$v}%")))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))->when($filters['consultant'] ?? null, fn (Builder $q, $v) => $q->where('assigned_user_id', $v));
    }

    private function consultants()
    {
        return User::role(['super_admin', 'administrator', 'tour_consultant'])->orderBy('name')->get();
    }

    private function statuses(): array
    {
        return ['new', 'contacted', 'planning', 'quotation_sent', 'confirmed', 'completed', 'cancelled'];
    }
}
