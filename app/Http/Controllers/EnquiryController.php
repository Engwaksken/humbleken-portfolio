<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use App\Models\User;
use App\Notifications\EnquiryReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request): RedirectResponse
    {
        $enquiry = Enquiry::create($request->safe()->except('website'));

        Notification::send(User::query()->where('is_admin', true)->get(), new EnquiryReceived($enquiry));

        return redirect()
            ->route('contact')
            ->with('enquiry_sent', true);
    }
}
