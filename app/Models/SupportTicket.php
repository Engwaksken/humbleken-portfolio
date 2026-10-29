<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    /** @use HasFactory<SupportTicketFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Owner and status are set by the application, never from request input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subject',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'open',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    /**
     * Replies the customer may see; internal staff notes are excluded.
     */
    public function visibleReplies(): HasMany
    {
        return $this->replies()->where('is_internal', false);
    }

    public function isClosed(): bool
    {
        return $this->status === TicketStatus::Closed;
    }

    /**
     * Add a reply and move the ticket to the status that follows from who wrote it.
     */
    public function addReply(User $author, string $body, bool $internal = false): TicketReply
    {
        $reply = new TicketReply(['body' => $body]);
        $reply->user()->associate($author);
        $reply->is_internal = $internal;
        $this->replies()->save($reply);

        if (! $internal) {
            $this->status = $author->is($this->user) ? TicketStatus::Open : TicketStatus::Answered;
            $this->last_activity_at = now();
            $this->save();
        }

        return $reply;
    }
}
