<?php

namespace App\Support;

use App\Models\BookingRequest;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Permanently deletes a customer account and the data linked to it, as required
 * by Google Play for apps that let people create accounts.
 */
class AccountDeletion
{
    public static function delete(User $user): void
    {
        // Administrator accounts are removed from the server, never self-service.
        if ($user->is_admin) {
            throw new InvalidArgumentException('Administrator accounts cannot be deleted this way.');
        }

        $attachmentPaths = $user->supportTickets()
            ->with('replies')
            ->get()
            ->flatMap(fn ($ticket) => $ticket->replies)
            ->flatMap(fn ($reply) => collect($reply->attachments ?? [])->pluck('path'))
            ->filter()
            ->values()
            ->all();

        DB::transaction(function () use ($user): void {
            // Replies are removed with their tickets (cascade).
            $user->supportTickets()->delete();
            BookingRequest::query()->where('user_id', $user->getKey())->delete();
            Enquiry::query()->where('user_id', $user->getKey())->delete();
            $user->tokens()->delete();
            $user->notifications()->delete();
            DB::table('sessions')->where('user_id', $user->getKey())->delete();
            $user->delete();
        });

        if ($attachmentPaths !== []) {
            Storage::disk('local')->delete($attachmentPaths);
        }
    }
}
