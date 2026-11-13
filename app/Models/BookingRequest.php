<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BookingRequest extends Model
{
    protected $fillable = ['service_id', 'user_id', 'name', 'email', 'phone', 'preferred_at', 'message'];

    protected $attributes = ['status' => 'new'];

    protected function casts(): array
    {
        return [
            'preferred_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BookingRequest $booking): void {
            $booking->reference ??= static::newReference();
        });
    }

    /** A short, unguessable code the customer uses to track the request. */
    public static function newReference(): string
    {
        do {
            $reference = 'BK-'.Str::upper(Str::random(8));
        } while (static::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Requests belonging to a customer: made while signed in, or made with their (verified) email.
     *
     * @param  Builder<BookingRequest>  $query
     */
    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->getKey());

            if ($user->hasVerifiedEmail()) {
                $query->orWhereRaw('lower(email) = ?', [Str::lower($user->email)]);
            }
        });
    }
}
