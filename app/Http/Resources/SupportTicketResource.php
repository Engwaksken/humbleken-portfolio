<?php

namespace App\Http\Resources;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SupportTicket */
class SupportTicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'is_closed' => $this->isClosed(),
            'created_at' => $this->created_at?->toIso8601String(),
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            // Only customer-visible replies are ever loaded into this relation.
            'replies' => $this->whenLoaded('visibleReplies', fn () => $this->visibleReplies->map(fn (TicketReply $reply): array => [
                'id' => $reply->id,
                'body' => $reply->body,
                'from_staff' => $reply->isFromStaff(),
                'author_name' => $reply->isFromStaff() ? (Settings::get('owner_name') ?: Settings::get('brand_name') ?: 'Support') : ($reply->user?->name ?? 'You'),
                'created_at' => $reply->created_at?->toIso8601String(),
                'attachments' => collect($reply->attachments ?? [])->map(fn (array $file, int $index): array => [
                    'index' => $index,
                    'name' => $file['name'] ?? basename($file['path'] ?? 'attachment'),
                    'download_url' => route('api.v1.tickets.attachments', ['ticket' => $this->id, 'reply' => $reply->id, 'index' => $index]),
                ])->values(),
            ])->values()),
        ];
    }
}
