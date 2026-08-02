<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactEnquiryRequest;
use App\Models\ContactEnquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', 'in:new,contacted,resolved,spam'], 'read' => ['nullable', 'in:read,unread']]);
        $enquiries = ContactEnquiry::query()
            ->when($filters['search'] ?? null, fn (Builder $q, string $v) => $q->where(fn (Builder $n) => $n->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")->orWhere('phone', 'like', "%{$v}%")->orWhere('subject', 'like', "%{$v}%")->orWhere('message', 'like', "%{$v}%")))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when(($filters['read'] ?? null) === 'read', fn (Builder $q) => $q->where('is_read', true))->when(($filters['read'] ?? null) === 'unread', fn (Builder $q) => $q->where('is_read', false))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.enquiries.contacts.index', compact('enquiries'));
    }

    public function show(ContactEnquiry $contactEnquiry): View
    {
        if (! $contactEnquiry->is_read) {
            $contactEnquiry->update(['is_read' => true]);
        }

        return view('admin.enquiries.contacts.show', ['enquiry' => $contactEnquiry]);
    }

    public function update(UpdateContactEnquiryRequest $request, ContactEnquiry $contactEnquiry): RedirectResponse
    {
        $contactEnquiry->update($request->validated());

        return back()->with('success', 'Contact enquiry updated.');
    }

    public function destroy(ContactEnquiry $contactEnquiry): RedirectResponse
    {
        abort_unless(auth()->user()->can('enquiries.update'), 403);
        abort_unless($contactEnquiry->status === 'spam', 422, 'Only enquiries marked as spam may be deleted.');
        $contactEnquiry->delete();

        return to_route('admin.contact-enquiries.index')->with('success', 'Spam enquiry deleted.');
    }
}
