<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.bookings.index', [
            'bookings' => BookingRequest::query()
                ->ownedBy($request->user())
                ->with('service')
                ->latest()
                ->paginate(15),
        ]);
    }
}
