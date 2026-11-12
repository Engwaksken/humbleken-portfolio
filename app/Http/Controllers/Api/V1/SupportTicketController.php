<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketActivity;
use App\Support\TicketAttachments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The signed-in customer's own support tickets. Other customers' tickets are simply not found.
 */
class SupportTicketController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return SupportTicketResource::collection(
            $request->user()->supportTickets()->latest('last_activity_at')->latest('id')->paginate(15)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            ...TicketAttachments::rules(),
        ]);

        $attachments = TicketAttachments::store($request);

        $ticket = DB::transaction(function () use ($request, $validated, $attachments): SupportTicket {
            $ticket = $request->user()->supportTickets()->create(['subject' => $validated['subject']]);
            $ticket->addReply($request->user(), $validated['body'], attachments: $attachments);

            return $ticket;
        });

        Notification::send(User::query()->where('is_admin', true)->get(), new TicketActivity($ticket, isNew: true));

        return $this->detail($ticket)->response()->setStatusCode(201);
    }

    public function show(Request $request, int $ticket): SupportTicketResource
    {
        return $this->detail($request->user()->supportTickets()->findOrFail($ticket));
    }

    public function reply(Request $request, int $ticket): JsonResponse
    {
        $ticket = $request->user()->supportTickets()->findOrFail($ticket);

        abort_if($ticket->isClosed(), 403, 'This ticket is closed.');

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:10000'],
            ...TicketAttachments::rules(),
        ]);

        $ticket->addReply($request->user(), $validated['body'], attachments: TicketAttachments::store($request));

        Notification::send(User::query()->where('is_admin', true)->get(), new TicketActivity($ticket, isNew: false));

        return $this->detail($ticket)->response()->setStatusCode(201);
    }

    public function attachment(Request $request, int $ticket, int $reply, int $index): StreamedResponse
    {
        $ticket = $request->user()->supportTickets()->findOrFail($ticket);
        $ticketReply = $ticket->visibleReplies()->findOrFail($reply);

        $file = $ticketReply->attachments[$index] ?? abort(404);
        abort_unless(is_array($file) && Storage::disk('local')->exists($file['path'] ?? ''), 404);

        return Storage::disk('local')->download($file['path'], $file['name'] ?? basename($file['path']));
    }

    private function detail(SupportTicket $ticket): SupportTicketResource
    {
        return new SupportTicketResource($ticket->fresh()->load('visibleReplies.user'));
    }
}
