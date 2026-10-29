<?php

namespace App\Http\Controllers\Account;

use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAttachmentController
{
    public function __invoke(Request $request, int $reply, int $attachment): StreamedResponse
    {
        $ticketReply = TicketReply::with('ticket')->findOrFail($reply);
        abort_if($ticketReply->is_internal && ! $request->user()->is_admin, 404);
        abort_unless($request->user()->is_admin || $ticketReply->ticket->user_id === $request->user()->id, 404);

        $file = $ticketReply->attachments[$attachment] ?? abort(404);
        abort_unless(Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}
