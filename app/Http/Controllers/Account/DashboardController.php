<?php

namespace App\Http\Controllers\Account;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tickets = $request->user()->supportTickets();

        return view('account.dashboard', [
            'recentTickets' => (clone $tickets)->latest('last_activity_at')->limit(5)->get(),
            'awaitingYou' => (clone $tickets)->where('status', TicketStatus::Answered)->count(),
        ]);
    }
}
