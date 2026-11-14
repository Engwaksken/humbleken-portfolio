<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\BookingRequest;
use App\Models\Enquiry;
use App\Models\User;
use App\Notifications\BookingRequestReceived;
use App\Notifications\ClientAutoReply;
use App\Notifications\EnquiryReceived;
use App\Support\AutoReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class SubmissionController extends Controller
{
    public function enquiry(StoreEnquiryRequest $request): JsonResponse
    {
        $enquiry = Enquiry::create($request->safe()->except('website'));

        Notification::send(User::query()->where('is_admin', true)->get(), new EnquiryReceived($enquiry));
        ClientAutoReply::sendFor($enquiry);

        $message = 'Thank you. Your enquiry has been received and will be reviewed shortly.';
        if (AutoReply::enabled('enquiry')) {
            $message .= ' A confirmation has been sent to your email.';
        }

        return response()->json(['message' => $message], 201);
    }

    public function booking(Request $request): JsonResponse
    {
        $data = $request->validate(BookingRequestController::rules());
        unset($data['website']);
        // The app sends local time with a UTC offset; store it in the site's time zone.
        $data['preferred_at'] = Carbon::parse($data['preferred_at'])->setTimezone(config('app.timezone'));

        $booking = BookingRequest::create($data + ['user_id' => $request->user('sanctum')?->getKey()]);

        Notification::send(User::query()->where('is_admin', true)->get(), new BookingRequestReceived($booking));
        ClientAutoReply::sendFor($booking);

        $message = 'Thank you. Your appointment request has been received. I will contact you to confirm availability.';
        if (AutoReply::enabled('booking')) {
            $message .= ' A confirmation with your reference has been sent to your email.';
        }

        return response()->json([
            'message' => $message,
            'reference' => $booking->reference,
        ], 201);
    }
}
