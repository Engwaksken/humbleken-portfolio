<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketActivity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.tickets.index', [
            'tickets' => $request->user()->supportTickets()->latest('last_activity_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('account.tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
        ]);

        $ticket = DB::transaction(function () use ($request, $validated): SupportTicket {
            $ticket = $request->user()->supportTickets()->create(['subject' => $validated['subject']]);
            $ticket->addReply($request->user(), $validated['body']);

            return $ticket;
        });

        Notification::send(User::query()->where('is_admin', true)->get(), new TicketActivity($ticket, isNew: true));

        return redirect()
            ->route('account.tickets.show', $ticket)
            ->with('status', 'Your ticket has been opened.');
    }

    public function show(Request $request, int $ticket): View
    {
        // Scoped to the signed-in customer: another customer's ticket is simply not found.
        $ticket = $request->user()->supportTickets()->findOrFail($ticket);

        return view('account.tickets.show', [
            'ticket' => $ticket,
            'replies' => $ticket->visibleReplies()->with('user')->get(),
        ]);
    }

    public function reply(Request $request, int $ticket): RedirectResponse
    {
        $ticket = $request->user()->supportTickets()->findOrFail($ticket);

        abort_if($ticket->isClosed(), 403, 'This ticket is closed.');

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:10000'],
        ]);

        $ticket->addReply($request->user(), $validated['body']);

        Notification::send(User::query()->where('is_admin', true)->get(), new TicketActivity($ticket, isNew: false));

        return redirect()
            ->route('account.tickets.show', $ticket)
            ->with('status', 'Your reply has been sent.');
    }
}
