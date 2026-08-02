<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StorePackageAvailabilityRequest;
use App\Models\Package;
use App\Models\WebsiteSetting;
use App\Notifications\NewPublicEnquiryNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Throwable;

class PackageAvailabilityController extends Controller
{
    public function store(StorePackageAvailabilityRequest $request, Package $package): RedirectResponse
    {
        abort_unless($package->is_active, 404);

        $data = $request->safe()->except('website');
        $enquiry = $package->enquiries()->create([
            ...$data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $email = WebsiteSetting::current()?->primary_email ?: config('mail.from.address');
        try {
            Notification::route('mail', $email)->notify(new NewPublicEnquiryNotification(
                'package availability enquiry',
                $enquiry->customer_name,
                $enquiry->email,
                $package->title,
                route('admin.package-enquiries.show', $enquiry),
            ));
        } catch (Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Thank you. Your availability request has been sent to our travel team.');
    }
}
