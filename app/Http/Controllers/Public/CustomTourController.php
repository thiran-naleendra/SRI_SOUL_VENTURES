<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreCustomTourRequest as StoreCustomTourFormRequest;
use App\Models\CustomTourRequest;
use App\Models\PageSection;
use App\Models\WebsiteSetting;
use App\Notifications\NewPublicEnquiryNotification;
use App\Support\PublicCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Throwable;

class CustomTourController extends Controller
{
    public function create(Request $request): View
    {
        $packages = PublicCache::packages();
        $selectedPackage = $request->string('package')->value()
            ? $packages->firstWhere('slug', $request->string('package')->value())
            : null;

        return view('public.pages.custom-tours', [
            'sections' => PageSection::where('page_key', 'custom_tours')->where('is_active', true)->orderBy('display_order')->get()->keyBy('section_key'),
            'packages' => $packages,
            'selectedPackage' => $selectedPackage,
            'destinations' => PublicCache::destinations(),
            'travelStyles' => PublicCache::travelStyles(),
        ]);
    }

    public function store(StoreCustomTourFormRequest $request): RedirectResponse
    {
        $validated = $request->safe()->except(['destination_ids', 'travel_style_ids', 'website']);

        $tourRequest = DB::transaction(function () use ($request, $validated): CustomTourRequest {
            $tourRequest = CustomTourRequest::create([
                ...Arr::except($validated, ['name', 'whatsapp']),
                'customer_name' => $validated['name'],
                'whatsapp_number' => $validated['whatsapp'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $tourRequest->destinations()->sync($request->validated('destination_ids', []));
            $tourRequest->travelStyles()->sync($request->validated('travel_style_ids', []));

            return $tourRequest;
        });

        $email = WebsiteSetting::current()?->primary_email ?: config('mail.from.address');
        try {
            Notification::route('mail', $email)->notify(new NewPublicEnquiryNotification(
                'custom tour request',
                $tourRequest->customer_name,
                $tourRequest->email,
                $tourRequest->package?->title ?: "Custom request #{$tourRequest->id}",
                route('admin.custom-tour-requests.show', $tourRequest),
            ));
        } catch (Throwable $exception) {
            report($exception);
        }

        return to_route('custom-tours.success');
    }

    public function success(): View
    {
        return view('public.pages.custom-tour-success');
    }
}
