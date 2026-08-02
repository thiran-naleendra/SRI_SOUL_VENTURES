<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreContactEnquiryRequest;
use App\Models\ContactEnquiry;
use App\Models\WebsiteSetting;
use App\Notifications\NewPublicEnquiryNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ContactEnquiryController extends Controller
{
    public function store(StoreContactEnquiryRequest $request): RedirectResponse
    {
        $enquiry = ContactEnquiry::create([
            ...$request->safe()->except('website'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $email = WebsiteSetting::current()?->primary_email ?: config('mail.from.address');
        try {
            Notification::route('mail', $email)->notify(new NewPublicEnquiryNotification(
                'contact enquiry',
                $enquiry->name,
                $enquiry->email,
                $enquiry->subject ?: "Contact enquiry #{$enquiry->id}",
                route('admin.contact-enquiries.show', $enquiry),
            ));
        } catch (Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Thank you for contacting us. Our travel team will reply shortly.');
    }
}
