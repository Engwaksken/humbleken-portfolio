<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\User;
use App\Notifications\BookingRequestReceived;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('booking', static::rules());
        unset($data['website']);

        $booking = BookingRequest::create($data + ['user_id' => $request->user()?->getKey()]);
        // Alert admins by email without coupling the request to a particular notification channel.
        Notification::send(User::query()->where('is_admin', true)->get(), new BookingRequestReceived($booking));

        return redirect()->route('booking')->with('booking_sent', $booking->reference);
    }

    /**
     * Look up a request by its reference and the email it was made with.
     */
    public function track(Request $request): View
    {
        $result = null;

        if ($request->isMethod('post')) {
            $data = $request->validateWithBag('track', [
                'reference' => ['required', 'string', 'max:20'],
                'email' => ['required', 'email', 'max:255'],
            ]);

            // Both must match, so a reference alone reveals nothing.
            $result = BookingRequest::query()
                ->with('service')
                ->where('reference', Str::upper(trim($data['reference'])))
                ->whereRaw('lower(email) = ?', [Str::lower(trim($data['email']))])
                ->first() ?? false;
        }

        return view('pages.booking-track', ['result' => $result]);
    }

    /**
     * Shared by the website form and the API.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'preferred_at' => ['required', 'date', 'after:now'],
            'message' => ['nullable', 'string', 'max:5000'],
            // Honeypot: hidden from people, often filled in by bots.
            'website' => ['prohibited'],
        ];
    }
}
