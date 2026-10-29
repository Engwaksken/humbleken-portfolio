<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\User;
use App\Notifications\BookingRequestReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class BookingRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('booking', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'preferred_at' => ['required', 'date', 'after:now'],
            'message' => ['nullable', 'string', 'max:5000'],
            'website' => ['prohibited'],
        ]);

        $booking = BookingRequest::create($data);
        // Alert admins by email without coupling the request to a particular notification channel.
        Notification::send(User::query()->where('is_admin', true)->get(), new BookingRequestReceived($booking));

        return redirect()->route('booking')->with('booking_sent', true);
    }
}
