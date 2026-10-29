<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketActivity;
use App\Support\ImageCompressor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

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
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,gif,txt,csv,doc,docx,xls,xlsx,zip'],
        ]);

        $attachments = $this->storeAttachments($request);

        $ticket = DB::transaction(function () use ($request, $validated, $attachments): SupportTicket {
            $ticket = $request->user()->supportTickets()->create(['subject' => $validated['subject']]);
            $ticket->addReply($request->user(), $validated['body'], attachments: $attachments);

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
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,gif,txt,csv,doc,docx,xls,xlsx,zip'],
        ]);

        $ticket->addReply($request->user(), $validated['body'], attachments: $this->storeAttachments($request));

        Notification::send(User::query()->where('is_admin', true)->get(), new TicketActivity($ticket, isNew: false));

        return redirect()
            ->route('account.tickets.show', $ticket)
            ->with('status', 'Your reply has been sent.');
    }

    private function storeAttachments(Request $request): array
    {
        return collect($request->file('attachments', []))->map(function ($file): array {
            $compressed = ImageCompressor::compress($file->getRealPath(), $file->getMimeType());
            $originalName = $file->getClientOriginalName();
            $path = $compressed === null
                ? $file->store('ticket-attachments', 'local')
                : 'ticket-attachments/'.Str::uuid().'.webp';

            if ($compressed !== null) {
                Storage::disk('local')->put($path, $compressed);
            }

            return [
                'path' => $path,
                'name' => $compressed === null ? $originalName : pathinfo($originalName, PATHINFO_FILENAME).'.webp',
            ];
        })->all();
    }
}
